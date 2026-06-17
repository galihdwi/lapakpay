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
        $config = [],
    ) {
        parent::__construct($config);
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
}
