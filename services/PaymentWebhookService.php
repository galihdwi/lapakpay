<?php

namespace app\services;

use app\jobs\ProcessPaymentWebhookJob;
use app\repositories\WebhookLogRepository;
use Yii;
use yii\base\Component;

class PaymentWebhookService extends Component
{
    public function __construct(
        private readonly WebhookLogRepository $webhookLogRepository,
        private readonly TransactionService $transactionService,
        $config = [],
    ) {
        parent::__construct($config);
    }

    public function process(string $gatewayName, array $payload, array $headers): array
    {
        $log = $this->webhookLogRepository->create($gatewayName, $payload, $headers, 'payment');

        try {
            $gateway = Yii::$app->get($this->gatewayComponentId($gatewayName));
            $data = $gateway->handleWebhook($payload, $headers);
            $processed = $this->transactionService->processPaymentWebhook($data, $gatewayName);

            $log->status = $processed ? 'processed' : 'failed';
            $log->notes = $processed
                ? 'Webhook processed for invoice ' . ($data['invoice_number'] ?? '-')
                : 'Transaction not found for invoice/reference.';
            $log->save(false);

            return [
                'status' => $processed ? 'success' : 'error',
                'processed' => $processed,
                'webhook_id' => (string) $log->_id,
                'invoice_number' => $data['invoice_number'] ?? null,
                'payment_status' => $data['status'] ?? null,
                'message' => $processed ? 'Webhook processed.' : 'Transaction not found.',
            ];
        } catch (\Throwable $exception) {
            $log->status = 'failed';
            $log->notes = $exception->getMessage();
            $log->save(false);

            throw $exception;
        }
    }

    public function enqueue(string $gatewayName, array $payload, array $headers): string
    {
        $log = $this->webhookLogRepository->create($gatewayName, $payload, $headers, 'payment');

        Yii::$app->get('queue')->push(new ProcessPaymentWebhookJob([
            'gatewayName' => $gatewayName,
            'payload' => $payload,
            'headers' => $headers,
            'webhookLogId' => (string) $log->_id,
        ]));

        return (string) $log->_id;
    }

    private function gatewayComponentId(string $gatewayName): string
    {
        return preg_replace('/[^a-z0-9]/i', '', $gatewayName) . 'Gateway';
    }
}
