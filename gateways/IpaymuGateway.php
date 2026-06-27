<?php

namespace app\gateways;

use app\interfaces\PaymentGatewayInterface;
use Yii;
use yii\base\Component;
use yii\helpers\Url;
use yii\httpclient\Client;

class IpaymuGateway extends Component implements PaymentGatewayInterface
{
    public string $apiUrl = 'https://my.ipaymu.com/api/v2/';
    public string $va = '';
    public string $apiKey = '';
    public string $publicBaseUrl = '';

    private Client $client;

    public function init(): void
    {
        parent::init();

        $apiUrl = $this->env('IPAYMU_API_URL');
        if ($apiUrl !== '') {
            $this->apiUrl = $apiUrl;
        }
        $this->apiUrl = $this->normalizeApiUrl($this->apiUrl);

        $va = $this->env('IPAYMU_VA');
        if (trim($this->va) === '' && $va !== '') {
            $this->va = $va;
        }
        $this->va = trim($this->va);

        $apiKey = $this->env('IPAYMU_API_KEY');
        if (trim($this->apiKey) === '' && $apiKey !== '') {
            $this->apiKey = $apiKey;
        }
        $this->apiKey = trim($this->apiKey);

        $publicBaseUrl = $this->env('APP_BASE_URL');
        if (trim($this->publicBaseUrl) === '' && $publicBaseUrl !== '') {
            $this->publicBaseUrl = $publicBaseUrl;
        }
        $this->publicBaseUrl = rtrim(trim($this->publicBaseUrl), '/');

        $this->client = new Client(['baseUrl' => $this->apiUrl]);
    }

    public function createInvoice(string $invoiceNumber, float $amount, string $paymentMethod, array $customerDetails): array
    {
        if (trim($this->va) === '' || trim($this->apiKey) === '') {
            return [
                'status' => 'error',
                'message' => 'IPAYMU_VA dan IPAYMU_API_KEY belum dikonfigurasi.',
            ];
        }

        $returnUrl = $this->publicUrl(['/payment/success', 'id' => $invoiceNumber]);
        $cancelUrl = $this->publicUrl(['/payment/failure', 'id' => $invoiceNumber]);
        $notifyUrl = $this->publicUrl(['/webhook/ipaymu']);

        if ($returnUrl === null || $cancelUrl === null || $notifyUrl === null) {
            return [
                'status' => 'error',
                'message' => 'APP_BASE_URL harus berupa URL publik HTTPS untuk redirect dan notify iPaymu.',
            ];
        }

        $payload = $this->sanitizePayload([
            'product' => [$this->cleanPaymentText('Invoice ' . $invoiceNumber)],
            'qty' => ['1'],
            'price' => [(string) (int) round($amount)],
            'description' => [$this->cleanPaymentText('Pembayaran transaksi ' . $invoiceNumber)],
            'returnUrl' => $returnUrl,
            'cancelUrl' => $cancelUrl,
            'notifyUrl' => $notifyUrl,
            'referenceId' => $invoiceNumber,
            'buyerEmail' => $customerDetails['email'] ?? '',
        ]);

        try {
            $body = $this->jsonEncode($payload);
            $response = $this->client
                ->post('payment')
                ->setHeaders($this->headers('POST', $body))
                ->setContent($body)
                ->send();
            $responseData = $this->responseData($response);

            if ($response->isOk) {
                $data = $responseData['Data'] ?? $responseData['data'] ?? $responseData;
                if (!is_array($data)) {
                    return [
                        'status' => 'error',
                        'message' => 'Response iPaymu tidak valid.',
                        'raw' => $responseData ?: $response->content,
                    ];
                }

                $paymentUrl = $this->normalizePaymentUrl($data['Url'] ?? $data['url'] ?? $data['paymentUrl'] ?? $data['payment_url'] ?? null);
                if ($paymentUrl === null) {
                    return [
                        'status' => 'error',
                        'message' => 'Response iPaymu tidak berisi URL pembayaran.',
                        'raw' => $responseData ?: $response->content,
                    ];
                }

                return [
                    'status' => 'success',
                    'id' => (string) ($data['SessionID'] ?? $data['sessionID'] ?? $data['session_id'] ?? $invoiceNumber),
                    'payment_url' => $paymentUrl,
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

    public function getPaymentStatus(string $reference): array
    {
        return [
            'status' => 'unknown',
            'message' => 'Status transaksi iPaymu diproses melalui webhook.',
            'gateway_reference' => $reference,
        ];
    }

    public function cancelInvoice(string $reference): bool
    {
        return false;
    }

    public function handleWebhook(array $payload, array $headers): array
    {
        return [
            'invoice_number' => $this->resolveInvoiceNumber($payload),
            'status' => $this->normalizeWebhookStatus($payload),
            'amount' => (float) ($payload['amount'] ?? $payload['Amount'] ?? $payload['total'] ?? 0),
            'gateway_reference' => (string) ($payload['trx_id'] ?? $payload['trxId'] ?? $payload['sid'] ?? $payload['SessionID'] ?? ''),
            'paid_at' => $payload['paid_at'] ?? $payload['paidAt'] ?? $payload['created_at'] ?? date('Y-m-d H:i:s'),
            'payment_method' => $payload['payment_method'] ?? $payload['paymentMethod'] ?? $payload['via'] ?? $payload['channel'] ?? null,
            'message' => $payload['system_notes'] ?? $payload['message'] ?? null,
        ];
    }

    private function headers(string $method, string $body): array
    {
        $timestamp = date('YmdHis');
        $bodyHash = strtolower(hash('sha256', $body));
        $va = trim($this->va);
        $apiKey = trim($this->apiKey);
        $stringToSign = strtoupper($method) . ':' . $va . ':' . $bodyHash . ':' . $apiKey;

        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'va' => $va,
            'signature' => hash_hmac('sha256', $stringToSign, $apiKey),
            'timestamp' => $timestamp,
        ];
    }

    private function publicUrl(array $route): ?string
    {
        if (!preg_match('/^https:\/\//i', $this->publicBaseUrl)) {
            return null;
        }

        return $this->publicBaseUrl . Url::to($route);
    }

    private function resolveInvoiceNumber(array $payload): ?string
    {
        foreach (['reference_id', 'referenceId', 'reference', 'invoice_number', 'invoiceNumber', 'sid', 'SessionID'] as $key) {
            if (!empty($payload[$key])) {
                return (string) $payload[$key];
            }
        }

        return null;
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));

        return match ($status) {
            'berhasil', 'success', 'sukses', 'paid', 'settled', 'completed', '1' => 'paid',
            'pending', '0' => 'pending',
            'expired', 'expire' => 'expired',
            'cancel', 'cancelled', 'canceled' => 'cancelled',
            'gagal', 'failed', 'fail', 'error', '2' => 'failed',
            default => $status !== '' ? $status : 'pending',
        };
    }

    private function normalizeWebhookStatus(array $payload): string
    {
        foreach (['status', 'Status', 'transaction_status', 'settlement_status'] as $key) {
            if (!empty($payload[$key])) {
                $status = $this->normalizeStatus((string) $payload[$key]);
                if ($status !== 'pending') {
                    return $status;
                }
            }
        }

        foreach (['status_code', 'transaction_status_code'] as $key) {
            if (array_key_exists($key, $payload)) {
                return $this->normalizeStatus((string) $payload[$key]);
            }
        }

        return 'pending';
    }

    private function resolveErrorMessage($data, string $content, int $statusCode): string
    {
        if (is_array($data)) {
            foreach (['Message', 'message', 'error', 'errors'] as $key) {
                if (isset($data[$key])) {
                    return 'iPaymu API: ' . (is_scalar($data[$key]) ? (string) $data[$key] : json_encode($data[$key]));
                }
            }
        }

        return $content !== '' ? 'iPaymu API HTTP ' . $statusCode . ': ' . $content : 'iPaymu API HTTP ' . $statusCode;
    }

    private function responseData($response): array
    {
        $data = $response->data;
        if (is_array($data)) {
            return $data;
        }

        try {
            $decoded = json_decode((string) $response->content, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $exception) {
            Yii::warning('iPaymu response could not be parsed: ' . $exception->getMessage(), __METHOD__);
            return [];
        }
    }

    private function normalizeApiUrl(string $apiUrl): string
    {
        $apiUrl = trim($apiUrl);
        return $apiUrl !== '' ? rtrim($apiUrl, '/') . '/' : 'https://my.ipaymu.com/api/v2/';
    }

    private function normalizePaymentUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        return $url !== '' ? $url : null;
    }

    private function jsonEncode(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function sanitizePayload(array $payload): array
    {
        $clean = [];

        foreach ($payload as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $value = $this->sanitizePayload($value);
                if ($value === []) {
                    continue;
                }
            } elseif (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    private function cleanPaymentText(string $value): string
    {
        return str_replace(['`', '‘', '’', '“', '”'], ["'", "'", "'", '"', '"'], $value);
    }

    private function env(string $name): string
    {
        if (function_exists('app_env')) {
            return app_env($name);
        }

        foreach ([getenv($name), $_ENV[$name] ?? null, $_SERVER[$name] ?? null] as $value) {
            if ($value !== false && $value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return '';
    }
}
