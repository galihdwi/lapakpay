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

    public function actionIpaymu()
    {
        return $this->processPaymentWebhook('ipaymu');
    }

    public function actionFlip()
    {
        return $this->processPaymentWebhook('flip');
    }

    private function processPaymentWebhook(string $gatewayName): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $payload = $this->requestPayload();
        $headers = Yii::$app->request->headers->toArray();

        try {
            $result = $this->paymentWebhookService->process($gatewayName, $payload, $headers);

            if (empty($result['processed'])) {
                Yii::$app->response->statusCode = 422;
            }

            return $result;
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;

            return ['status' => 'error', 'message' => $exception->getMessage()];
        }
    }

    private function requestPayload(): array
    {
        $payload = Yii::$app->request->post();
        if ($payload !== []) {
            return $payload;
        }

        $rawBody = Yii::$app->request->rawBody;
        if (trim($rawBody) === '') {
            return [];
        }

        try {
            $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $exception) {
            Yii::warning('Webhook body is not valid JSON: ' . $exception->getMessage(), __METHOD__);
            return [];
        }
    }
}
