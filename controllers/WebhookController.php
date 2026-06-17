<?php

namespace app\controllers;

use app\services\PaymentWebhookService;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class WebhookController extends Controller
{
    public $enableCsrfValidation = false;

    public function __construct(
        $id,
        $module,
        private readonly PaymentWebhookService $paymentWebhookService,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionMayar()
    {
        return $this->enqueuePaymentWebhook('mayar');
    }

    private function enqueuePaymentWebhook(string $gatewayName): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $payload = Yii::$app->request->post();
        $headers = Yii::$app->request->headers->toArray();

        try {
            $webhookId = $this->paymentWebhookService->enqueue($gatewayName, $payload, $headers);

            return ['status' => 'queued', 'webhook_id' => $webhookId];
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return ['status' => 'error', 'message' => $exception->getMessage()];
        }
    }
}
