<?php

namespace app\jobs;

use app\services\TransactionService;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ProcessPaymentWebhookJob extends BaseObject implements JobInterface
{
    public string $gatewayName = '';
    public array $payload = [];
    public array $headers = [];
    public string $webhookLogId = '';

    public function execute($queue): void
    {
        try {
            $gatewayName = 'mayar';
            $gateway = Yii::$app->get('mayarGateway');
            $data = $gateway->handleWebhook($this->payload, $this->headers);

            Yii::createObject(TransactionService::class)->processPaidPayment($data, $gatewayName);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw $exception;
        }
    }
}
