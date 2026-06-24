<?php

namespace app\gateways;

use app\interfaces\PaymentGatewayInterface;
use Yii;
use yii\base\Component;
use yii\helpers\Url;
use yii\httpclient\Client;

class FlipGateway extends Component implements PaymentGatewayInterface
{
    public $apiUrl = 'https://bigflip.id/big_sandbox_api/v2/';
    public $apiKey = '';
    public $validationToken = '';
    public $publicBaseUrl = '';

    private Client $client;

    public function init(): void
    {
        parent::init();

        $apiUrl = $this->env('FLIP_API_URL');
        if ($apiUrl !== '') {
            $this->apiUrl = $apiUrl;
        }
        $this->apiUrl = $this->normalizeApiUrl($this->apiUrl);

        $apiKey = $this->env('FLIP_API_KEY');
        if (trim((string) $this->apiKey) === '' && $apiKey !== '') {
            $this->apiKey = $apiKey;
        }

        $validationToken = $this->env('FLIP_VALIDATION_TOKEN');
        if (trim((string) $this->validationToken) === '' && $validationToken !== '') {
            $this->validationToken = $validationToken;
        }

        $publicBaseUrl = $this->env('APP_BASE_URL');
        if (trim((string) $this->publicBaseUrl) === '' && $publicBaseUrl !== '') {
            $this->publicBaseUrl = $publicBaseUrl;
        }
        $this->publicBaseUrl = rtrim(trim((string) $this->publicBaseUrl), '/');

        $this->client = new Client(['baseUrl' => $this->apiUrl]);
    }

    public function createInvoice(string $invoiceNumber, float $amount, string $paymentMethod, array $customerDetails): array
    {
        if (trim((string) $this->apiKey) === '') {
            return [
                'status' => 'error',
                'message' => 'FLIP_API_KEY belum dikonfigurasi.',
            ];
        }

        $redirectUrl = $this->redirectUrl($invoiceNumber);
        if ($redirectUrl === null) {
            return [
                'status' => 'error',
                'message' => 'APP_BASE_URL harus berupa URL publik HTTPS untuk redirect Flip, contoh: https://domain-anda.com.',
            ];
        }

        $expiredAt = date('Y-m-d H:i', time() + 86400);
        $payload = [
            'title' => 'Invoice ' . $invoiceNumber,
            'amount' => (int) round($amount),
            'type' => 'SINGLE',
            'expired_date' => $expiredAt,
            'redirect_url' => $redirectUrl,
            'sender_name' => $customerDetails['name'] ?? 'Customer',
            'sender_email' => $customerDetails['email'] ?? '',
            'sender_phone_number' => $customerDetails['mobile'] ?? $customerDetails['phone'] ?? '',
            'is_address_required' => 0,
            'is_phone_number_required' => 0,
        ];

        try {
            $response = $this->client
                ->post('pwf/bill', $payload, $this->headers())
                ->setFormat(Client::FORMAT_URLENCODED)
                ->send();
            $responseData = $this->responseData($response);

            $statusCode = (int) $response->statusCode;

            if ($response->isOk || $statusCode === 201) {
                $data = $responseData['data'] ?? $responseData;

                if (!is_array($data)) {
                    return [
                        'status' => 'error',
                        'message' => 'Response Flip tidak valid.',
                        'raw' => $responseData ?: $response->content,
                    ];
                }

                return [
                    'status' => 'success',
                    'id' => (string) ($data['id'] ?? $data['bill_link_id'] ?? $data['link_id'] ?? $invoiceNumber),
                    'payment_url' => $this->normalizePaymentUrl($data['bill_link'] ?? $data['link_url'] ?? $data['payment_url'] ?? null),
                    'expiry_date' => $data['expired_date'] ?? $data['expiry_date'] ?? $expiredAt,
                    'raw' => $responseData ?: $response->content,
                ];
            }

            return [
                'status' => 'error',
                'message' => $this->resolveErrorMessage($responseData, $response->content, $statusCode),
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
            $response = $this->client->get('pwf/bill/' . rawurlencode($reference), [], $this->headers())->send();
            $responseData = $this->responseData($response);

            if ($response->isOk) {
                $data = $responseData['data'] ?? $responseData;
                return [
                    'status' => $this->normalizeStatus($data['status'] ?? 'pending'),
                    'raw' => $responseData ?: $response->content,
                ];
            }

            return [
                'status' => 'error',
                'message' => $this->resolveErrorMessage($responseData, $response->content, (int) $response->statusCode),
                'raw' => $responseData ?: $response->content,
            ];
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => $exception->getMessage()];
        }
    }

    public function cancelInvoice(string $reference): bool
    {
        try {
            $response = $this->client
                ->post('pwf/bill/' . rawurlencode($reference) . '/cancel', [], $this->headers())
                ->send();

            return $response->isOk;
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return false;
        }
    }

    public function handleWebhook(array $payload, array $headers): array
    {
        $this->validateWebhookToken($headers);

        $data = $payload['data'] ?? $payload;
        if (isset($data['bill']) && is_array($data['bill'])) {
            $data = $data['bill'];
        }

        return [
            'invoice_number' => $this->resolveInvoiceNumber($data),
            'status' => $this->normalizeStatus($data['status'] ?? $data['payment_status'] ?? 'paid'),
            'amount' => (float) ($data['amount'] ?? $data['payment_amount'] ?? 0),
            'gateway_reference' => (string) ($data['id'] ?? $data['bill_link_id'] ?? $data['link_id'] ?? $data['reference_id'] ?? ''),
            'paid_at' => $data['payment_time'] ?? $data['paid_at'] ?? $data['updated_at'] ?? date('Y-m-d H:i:s'),
            'payment_method' => $data['payment_method'] ?? $data['sender_bank'] ?? null,
        ];
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
    }

    private function normalizePaymentUrl($url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        return 'https://' . ltrim($url, '/');
    }

    private function redirectUrl(string $invoiceNumber): ?string
    {
        $path = '/payment/success?id=' . rawurlencode($invoiceNumber);
        $url = $this->publicBaseUrl !== ''
            ? $this->publicBaseUrl . '/' . ltrim($path, '/')
            : $this->currentRequestUrl($invoiceNumber);

        if (!$this->isPublicHttpsUrl($url)) {
            return null;
        }

        return $url;
    }

    private function currentRequestUrl(string $invoiceNumber): string
    {
        try {
            return Url::to(['/payment/success', 'id' => $invoiceNumber], true);
        } catch (\Throwable) {
            return '';
        }
    }

    private function isPublicHttpsUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return false;
        }

        $host = strtolower((string) $parts['host']);
        return !in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true)
            && !str_ends_with($host, '.local')
            && !str_ends_with($host, '.test');
    }

    private function validateWebhookToken(array $headers): void
    {
        if (trim((string) $this->validationToken) === '') {
            return;
        }

        $token = $this->headerValue($headers, 'x-flip-validation-token')
            ?: $this->headerValue($headers, 'validation-token');

        if (!hash_equals((string) $this->validationToken, (string) $token)) {
            throw new \RuntimeException('Token webhook Flip tidak valid.');
        }
    }

    private function headerValue(array $headers, string $name): ?string
    {
        foreach ($headers as $key => $value) {
            if (strtolower((string) $key) !== strtolower($name)) {
                continue;
            }

            return is_array($value) ? (string) reset($value) : (string) $value;
        }

        return null;
    }

    private function resolveInvoiceNumber(array $data): string
    {
        foreach (['external_id', 'externalId', 'invoice_number', 'reference_id'] as $key) {
            if (!empty($data[$key])) {
                return (string) $data[$key];
            }
        }

        $title = (string) ($data['title'] ?? '');
        if (preg_match('/INV-[A-Z0-9-]+/i', $title, $matches) === 1) {
            return strtoupper($matches[0]);
        }

        return '';
    }

    private function normalizeStatus($status): string
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'successful', 'success', 'settled', 'paid' => 'paid',
            'cancelled', 'canceled', 'failed', 'expired' => $status,
            default => $status !== '' ? $status : 'pending',
        };
    }

    private function resolveErrorMessage($data, string $content, int $statusCode): string
    {
        if (is_array($data)) {
            if (isset($data['message'])) {
                return 'Flip API: ' . (string) $data['message'];
            }

            if (isset($data['errors'])) {
                return 'Flip API: ' . json_encode($data['errors']);
            }
        }

        if (trim($content) !== '') {
            return 'Flip API HTTP ' . $statusCode . ': ' . $content;
        }

        return 'Flip API HTTP ' . $statusCode;
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
            Yii::warning('Flip response could not be parsed: ' . $exception->getMessage(), __METHOD__);
            return [];
        }
    }

    private function normalizeApiUrl(string $apiUrl): string
    {
        $apiUrl = trim($apiUrl);
        if ($apiUrl === '') {
            return 'https://bigflip.id/big_sandbox_api/v2/';
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
