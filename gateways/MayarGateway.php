<?php

namespace app\gateways;

use app\interfaces\PaymentGatewayInterface;
use Yii;
use yii\base\Component;
use yii\httpclient\Client;

class MayarGateway extends Component implements PaymentGatewayInterface
{
    public $apiUrl = 'https://api.mayar.id/';
    public $apiKey = '';

    private $client;

    public function init(): void
    {
        parent::init();

        $apiUrl = $this->env('MAYAR_API_URL');
        if ($apiUrl !== '') {
            $this->apiUrl = $apiUrl;
        }
        $this->apiUrl = $this->normalizeApiUrl($this->apiUrl);

        $apiKey = $this->env('MAYAR_API_KEY');
        if (trim((string) $this->apiKey) === '' && $apiKey !== '') {
            $this->apiKey = $apiKey;
        }

        $this->client = new Client(['baseUrl' => $this->apiUrl]);
    }

    public function createInvoice(string $invoiceNumber, float $amount, string $paymentMethod, array $customerDetails): array
    {
        if (trim($this->apiKey) === '') {
            return [
                'status' => 'error',
                'message' => 'MAYAR_API_KEY belum dikonfigurasi.',
            ];
        }

        $payload = [
            'name' => $customerDetails['name'] ?? 'Customer',
            'email' => $customerDetails['email'] ?? '',
            'amount' => (int) round($amount),
            'mobile' => $customerDetails['mobile'] ?? $customerDetails['phone'] ?? '',
            'redirectURL' => \yii\helpers\Url::to(['/payment/success', 'id' => $invoiceNumber], true),
            'redirectUrl' => \yii\helpers\Url::to(['/payment/success', 'id' => $invoiceNumber], true),
            'description' => 'Invoice ' . $invoiceNumber,
            'expiredAt' => gmdate('c', time() + 86400),
        ];

        try {
            $response = $this->client
                ->post('hl/v1/payment/create', $payload, $this->headers())
                ->setFormat(Client::FORMAT_JSON)
                ->send();
            $responseData = $this->responseData($response);

            if ($response->isOk) {
                $data = $responseData['data'] ?? $responseData;
                if (isset($data[0]) && is_array($data[0])) {
                    $data = $data[0];
                }

                if (!is_array($data)) {
                    return [
                        'status' => 'error',
                        'message' => 'Response Mayar tidak valid.',
                        'raw' => $responseData ?: $response->content,
                    ];
                }

                return [
                    'status' => 'success',
                    'id' => (string) ($data['transactionId'] ?? $data['transaction_id'] ?? $data['id'] ?? $invoiceNumber),
                    'payment_url' => $data['link'] ?? null,
                    'expiry_date' => $data['expiredAt'] ?? null,
                    'raw' => $responseData ?: $response->content,
                ];
            }

            return [
                'status' => 'error',
                'message' => $this->resolveErrorMessage($responseData, $response->content, $response->statusCode),
                'raw' => $responseData ?: $response->content,
            ];
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => $exception->getMessage()];
        }
    }

    public function getPaymentStatus(string $reference): array
    {
        try {
            $response = $this->client->get('hl/v1/payment/' . $reference, [], $this->headers())->send();
            $responseData = $this->responseData($response);

            if ($response->isOk) {
                $data = $responseData['data'] ?? $responseData;
                return [
                    'status' => strtolower((string) ($data['status'] ?? 'pending')),
                    'raw' => $responseData ?: $response->content,
                ];
            }

            return ['status' => 'error', 'message' => $response->content];
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => $exception->getMessage()];
        }
    }

    public function cancelInvoice(string $reference): bool
    {
        try {
            $response = $this->client->post('hl/v1/payment/' . $reference . '/cancel', [], $this->headers())->send();
            return $response->isOk;
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return false;
        }
    }

    public function handleWebhook(array $payload, array $headers): array
    {
        $data = $payload['data'] ?? $payload;

        return [
            'invoice_number' => (string) ($data['externalId'] ?? $data['external_id'] ?? ''),
            'status' => strtolower((string) ($data['status'] ?? 'paid')),
            'amount' => (float) ($data['amount'] ?? 0),
            'gateway_reference' => (string) ($data['transactionId'] ?? $data['transaction_id'] ?? $data['id'] ?? ''),
            'paid_at' => $data['paidAt'] ?? $data['paid_at'] ?? date('Y-m-d H:i:s'),
            'payment_method' => $data['paymentMethod'] ?? null,
        ];
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    private function resolveErrorMessage($data, string $content, int $statusCode): string
    {
        if (is_array($data)) {
            if (isset($data['messages'])) {
                return 'Mayar API: ' . (string) $data['messages'];
            }

            if (isset($data['message'])) {
                return 'Mayar API: ' . (string) $data['message'];
            }

            if (isset($data['errors'])) {
                return 'Mayar API: ' . json_encode($data['errors']);
            }
        }

        if (trim($content) !== '') {
            return 'Mayar API HTTP ' . $statusCode . ': ' . $content;
        }

        return 'Mayar API HTTP ' . $statusCode;
    }

    private function responseData($response): array
    {
        $content = trim((string) $response->content);
        if ($content !== '') {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        try {
            $data = $response->data;
            return is_array($data) ? $data : [];
        } catch (\Throwable $exception) {
            Yii::warning('Mayar response could not be parsed: ' . $exception->getMessage(), __METHOD__);
            return [];
        }
    }

    private function normalizeApiUrl(string $apiUrl): string
    {
        $apiUrl = trim($apiUrl);
        if ($apiUrl === '' || str_contains($apiUrl, 'api.mayar.club')) {
            return 'https://api.mayar.id/';
        }

        return rtrim($apiUrl, '/') . '/';
    }

    private function env(string $name): string
    {
        $envFile = dirname(__DIR__) . '/config/env.php';
        if (is_file($envFile)) {
            require_once $envFile;
        }

        if (function_exists('app_env')) {
            return app_env($name);
        }

        $value = getenv($name);
        if (($value === false || $value === '') && isset($_ENV[$name])) {
            $value = $_ENV[$name];
        }
        if (($value === false || $value === '') && isset($_SERVER[$name])) {
            $value = $_SERVER[$name];
        }

        return trim((string) ($value === false ? '' : $value));
    }
}
