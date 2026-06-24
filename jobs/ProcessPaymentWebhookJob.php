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
            $gatewayName = $this->gatewayName !== '' ? $this->gatewayName : (string) (Yii::$app->params['paymentGateway'] ?? 'flip');
            $gateway = Yii::$app->get($this->gatewayComponentId($gatewayName));
            $data = $gateway->handleWebhook($this->payload, $this->headers);

            Yii::createObject(TransactionService::class)->processPaidPayment($data, $gatewayName);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw $exception;
        }
    }

    private function gatewayComponentId(string $gatewayName): string
    {
        return preg_replace('/[^a-z0-9]/i', '', $gatewayName) . 'Gateway';
    }
}
