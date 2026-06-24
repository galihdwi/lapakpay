<?php

namespace app\services;

use app\repositories\ProductRepository;
use app\repositories\TransactionRepository;
use app\services\ProviderRegistry;
use Yii;
use yii\base\Component;

class OrderService extends Component
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly ProductRepository $productRepository,
        private readonly ProviderRegistry $providerRegistry,
        $config = [],
    ) {
        parent::__construct($config);
    }

    public function sendTransactionToSupplier(string $transactionId): void
    {
        $transaction = $this->transactionRepository->findById($transactionId);
        if ($transaction === null || $transaction->status !== 'processing') {
            return;
        }

        $product = $this->productRepository->findById($transaction->product_id);
        if ($product === null) {
            $transaction->status = 'error';
            $transaction->notes = 'Product not found';
            $this->transactionRepository->save($transaction);
            return;
        }

        try {
            $provider = $this->providerRegistry->get((string) $product->provider);
            $result = $provider->order(
                (string) $product->provider_code,
                (string) $transaction->target,
                $transaction->zone ? (string) $transaction->zone : null,
                (string) $transaction->invoice_number,
            );

            if (isset($result['data']['trxid'])) {
                $transaction->trxid_provider = (string) $result['data']['trxid'];
                $transaction->status = (string) ($result['data']['status'] ?? 'success');
                $this->transactionRepository->save($transaction);
                return;
            }

            throw new \RuntimeException($result['message'] ?? 'Supplier order failed');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $transaction->status = 'error';
            $transaction->notes = $exception->getMessage();
            $this->transactionRepository->save($transaction);
        }
    }
}
