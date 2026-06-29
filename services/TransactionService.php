<?php

namespace app\services;

use app\jobs\SupplierOrderJob;
use app\models\Product;
use app\models\Transaction;
use app\repositories\ProductRepository;
use app\repositories\TransactionRepository;
use app\repositories\PaymentRepository;
use Yii;
use yii\base\Component;
use yii\base\Exception;

class TransactionService extends Component
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly ProductRepository $productRepository,
        $config = [],
    ) {
        parent::__construct($config);
    }

    public function createInvoiceNumber(): string
    {
        return 'INV' . date('ymdHis') . random_int(100, 999);
    }

    public function createTopupTransaction(
        Product $product,
        string $target,
        ?string $zone,
        string $paymentMethod,
        ?string $email = null,
        ?string $userId = null,
        ?string $nickname = null,
    ): Transaction {
        $transaction = new Transaction([
            'invoice_number' => $this->createInvoiceNumber(),
            'user_id' => $userId,
            'email' => $email,
            'product_id' => (string) $product->_id,
            'target' => $target,
            'zone' => $zone,
            'nickname' => $nickname,
            'provider' => $product->provider,
            'payment_method' => $paymentMethod,
            'buy_price' => (float) $product->base_price,
            'sell_price' => $this->resolveSellPrice($product),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $transaction->profit = $transaction->sell_price - $transaction->buy_price;

        if (!$this->transactionRepository->save($transaction)) {
            throw new Exception('Failed to create transaction: ' . json_encode($transaction->errors));
        }

        return $transaction;
    }

    public function createPayment(Transaction $transaction, array $customerDetails = []): array
    {
        $gatewayName = $this->defaultGatewayName();
        $invoice = Yii::$app->get($this->gatewayComponentId($gatewayName))->createInvoice(
            (string) $transaction->invoice_number,
            (float) $transaction->sell_price,
            (string) $transaction->payment_method,
            $customerDetails,
        );

        if (($invoice['status'] ?? null) !== 'success') {
            return $invoice;
        }

        $transaction->payment_gateway = $gatewayName;
        $this->transactionRepository->save($transaction, false, ['payment_gateway']);

        $payment = $this->paymentRepository->getOrCreateByInvoiceNumber((string) $transaction->invoice_number);
        $payment->setAttributes([
            'invoice_number' => $transaction->invoice_number,
            'amount' => (float) $transaction->sell_price,
            'gateway' => $gatewayName,
            'gateway_reference' => $invoice['id'] ?? null,
            'status' => 'pending',
        ], false);
        $this->paymentRepository->save($payment, false);

        return $invoice;
    }

    public function processPaidPayment(array $data, string $gatewayName): bool
    {
        return $this->processPaymentWebhook($data, $gatewayName);
    }

    public function processPaymentWebhook(array $data, string $gatewayName): bool
    {
        $transaction = $this->transactionRepository->findByInvoiceNumber($data['invoice_number'] ?? null);
        $payment = null;

        if ($transaction === null) {
            $payment = $this->paymentRepository->findByGatewayReference($data['gateway_reference'] ?? null);
            if ($payment !== null) {
                $transaction = $this->transactionRepository->findByInvoiceNumber($payment->invoice_number);
            }
        }

        if ($transaction === null) {
            return false;
        }

        $payment = $payment ?: $this->paymentRepository->getOrCreateByInvoiceNumber((string) $transaction->invoice_number);
        $paymentStatus = (string) ($data['status'] ?? 'pending');
        $previousTransactionStatus = (string) $transaction->status;
        $payment->setAttributes([
            'invoice_number' => $transaction->invoice_number,
            'amount' => (float) ($data['amount'] ?? $transaction->sell_price),
            'gateway' => $gatewayName,
            'gateway_reference' => $data['gateway_reference'] ?? $payment->gateway_reference,
            'status' => $paymentStatus,
            'paid_at' => $data['paid_at'] ?? date('Y-m-d H:i:s'),
        ], false);
        $this->paymentRepository->save($payment, false);

        if (in_array($paymentStatus, ['paid', 'settled', 'success'], true) && $transaction->status === 'pending') {
            $transaction->status = 'processing';
            $transaction->payment_gateway = $gatewayName;
            $this->transactionRepository->save($transaction, false, ['status', 'payment_gateway']);
            $this->sendPaymentStatusNotification($transaction, $paymentStatus, $previousTransactionStatus);

            Yii::$app->get('queue')->push(new SupplierOrderJob([
                'transaction_id' => (string) $transaction->_id,
            ]));
        } elseif (in_array($paymentStatus, ['failed', 'expired', 'cancelled'], true) && $transaction->status === 'pending') {
            $transaction->status = $paymentStatus;
            $transaction->payment_gateway = $gatewayName;
            $transaction->notes = $data['message'] ?? $transaction->notes;
            $this->transactionRepository->save($transaction, false, ['status', 'payment_gateway', 'notes']);
            $this->sendPaymentStatusNotification($transaction, $paymentStatus, $previousTransactionStatus);
        }

        return true;
    }

    private function sendPaymentStatusNotification(
        Transaction $transaction,
        string $paymentStatus,
        string $previousTransactionStatus,
    ): void {
        $email = trim((string) $transaction->email);
        if ($email === '' || $previousTransactionStatus !== 'pending' || !Yii::$app->has('resendEmail')) {
            return;
        }

        $product = $this->productRepository->findById((string) $transaction->product_id);

        Yii::$app->resendEmail->sendPaymentStatusNotification(
            $email,
            $transaction,
            $product,
            $paymentStatus,
        );
    }

    private function defaultGatewayName(): string
    {
        return (string) (Yii::$app->params['paymentGateway'] ?? 'flip');
    }

    private function gatewayComponentId(string $gatewayName): string
    {
        return preg_replace('/[^a-z0-9]/i', '', $gatewayName) . 'Gateway';
    }

    private function resolveSellPrice(Product $product): float
    {
        $user = Yii::$app->user ?? null;

        if ($user !== null && !$user->isGuest && $user->identity->role === 'reseller') {
            return (float) $product->reseller_price;
        }

        return (float) $product->user_price;
    }
}
