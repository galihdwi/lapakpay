<?php

namespace app\gateways;

use app\interfaces\PaymentGatewayInterface;
use yii\httpclient\Client;
use yii\base\Component;

/**
 * TripayGateway implementation for Tripay Payment Gateway.
 */
class TripayGateway extends Component implements PaymentGatewayInterface
{
    public $apiUrl;
    public $apiKey;
    public $privateKey;
    public $merchantCode;

    private $_client;

    public function init()
    {
        parent::init();
        $this->_client = new Client([
            'baseUrl' => $this->apiUrl,
        ]);
    }

    public function createInvoice(string $invoiceNumber, float $amount, string $paymentMethod, array $customerDetails): array
    {
        $signature = hash_hmac('sha256', $this->merchantCode . $invoiceNumber . $amount, $this->privateKey);

        $payload = [
            'method'         => $paymentMethod,
            'merchant_ref'   => $invoiceNumber,
            'amount'         => $amount,
            'customer_name'  => $customerDetails['name'] ?? 'Customer',
            'customer_email' => $customerDetails['email'] ?? 'customer@example.com',
            'customer_phone' => $customerDetails['phone'] ?? '',
            'order_items'    => [
                [
                    'sku'      => 'TOPUP',
                    'name'     => 'Topup Product',
                    'price'    => $amount,
                    'quantity' => 1,
                ]
            ],
            'callback_url'   => \yii\helpers\Url::to(['/webhook/tripay'], true),
            'return_url'     => \yii\helpers\Url::to(['/payment/success', 'id' => $invoiceNumber], true),
            'expired_time'   => (time() + (24 * 60 * 60)), // 24 hours
            'signature'      => $signature,
        ];

        $response = $this->_client->post('transaction/create', $payload, [
            'Authorization' => 'Bearer ' . $this->apiKey
        ])->send();

        if ($response->isOk && $response->data['success']) {
            return [
                'status' => 'success',
                'id' => $response->data['data']['reference'],
                'payment_url' => $response->data['data']['checkout_url'],
                'expiry_date' => date('Y-m-d H:i:s', $response->data['data']['expired_time']),
            ];
        }

        return [
            'status' => 'error',
            'message' => $response->data['message'] ?? 'Unknown Error',
        ];
    }

    public function checkPayment(string $reference): array
    {
        $response = $this->_client->get('transaction/detail', ['reference' => $reference], [
            'Authorization' => 'Bearer ' . $this->apiKey
        ])->send();

        if ($response->isOk && $response->data['success']) {
            return [
                'status' => strtolower($response->data['data']['status']),
                'raw' => $response->data['data'],
            ];
        }

        return ['status' => 'error'];
    }

    public function cancelPayment(string $reference): bool
    {
        // Tripay doesn't explicitly have a cancel endpoint in basic API
        return false;
    }

    public function handleWebhook(array $payload, array $headers): array
    {
        $callbackSignature = $headers['x-callback-signature'] ?? '';
        $json = json_encode($payload);
        $signature = hash_hmac('sha256', $json, $this->privateKey);

        if ($callbackSignature !== $signature) {
            throw new \Exception("Invalid Signature");
        }

        return [
            'invoice_number' => $payload['merchant_ref'],
            'status' => strtolower($payload['status']),
            'amount' => $payload['amount'],
            'paid_at' => date('Y-m-d H:i:s', $payload['paid_at'] ?? time()),
            'payment_method' => $payload['payment_method'],
        ];
    }
}
