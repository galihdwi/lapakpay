<?php

namespace app\services;

use app\jobs\SupplierOrderJob;
use app\models\Product;
use app\models\Transaction;
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
        $config = [],
    ) {
        parent::__construct($config);
    }

    public function createInvoiceNumber(): string
    {
        return 'INV-' . date('YmdHis') . '-' . strtoupper(Yii::$app->security->generateRandomString(6));
    }

    public function createTopupTransaction(
        Product $product,
        string $target,
        ?string $zone,
        string $paymentMethod,
        ?string $userId = null,
        ?string $nickname = null,
    ): Transaction {
        $transaction = new Transaction([
            'invoice_number' => $this->createInvoiceNumber(),
            'user_id' => $userId,
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
        $gatewayName = 'mayar';
        $invoice = Yii::$app->get('mayarGateway')->createInvoice(
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
        $payment->setAttributes([
            'invoice_number' => $transaction->invoice_number,
            'amount' => (float) ($data['amount'] ?? $transaction->sell_price),
            'gateway' => $gatewayName,
            'gateway_reference' => $data['gateway_reference'] ?? $payment->gateway_reference,
            'status' => $data['status'] ?? 'paid',
            'paid_at' => $data['paid_at'] ?? date('Y-m-d H:i:s'),
        ], false);
        $this->paymentRepository->save($payment, false);

        if (in_array($data['status'] ?? '', ['paid', 'settled', 'success'], true) && $transaction->status === 'pending') {
            $transaction->status = 'processing';
            $transaction->payment_gateway = $gatewayName;
            $this->transactionRepository->save($transaction, false, ['status', 'payment_gateway']);

            Yii::$app->get('queue')->push(new SupplierOrderJob([
                'transaction_id' => (string) $transaction->_id,
            ]));
        }

        return true;
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
