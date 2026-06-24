<?php

namespace app\gateways;

use app\interfaces\PaymentGatewayInterface;
use Xendit\Xendit;
use Xendit\Invoice\InvoiceApi;
use yii\base\Component;

/**
 * XenditGateway implementation for Xendit Payment Gateway.
 */
class XenditGateway extends Component implements PaymentGatewayInterface
{
    public $apiKey;
    private $_apiInstance;

    public function init()
    {
        parent::init();
        Xendit::setApiKey($this->apiKey);
        $this->_apiInstance = new InvoiceApi();
    }

    public function createInvoice(string $invoiceNumber, float $amount, string $paymentMethod, array $customerDetails): array
    {
        $params = [
            'external_id' => $invoiceNumber,
            'amount' => $amount,
            'description' => 'Payment for Invoice #' . $invoiceNumber,
            'customer' => [
                'given_names' => $customerDetails['name'] ?? 'Customer',
                'email' => $customerDetails['email'] ?? '',
                'mobile_number' => $customerDetails['phone'] ?? '',
            ],
            'payment_methods' => [$paymentMethod],
            'success_redirect_url' => \yii\helpers\Url::to(['/payment/success', 'id' => $invoiceNumber], true),
            'failure_redirect_url' => \yii\helpers\Url::to(['/payment/failure', 'id' => $invoiceNumber], true),
        ];

        try {
            $result = $this->_apiInstance->createInvoice($params);
            return [
                'status' => 'success',
                'id' => $result['id'],
                'payment_url' => $result['invoice_url'],
                'expiry_date' => $result['expiry_date'],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function checkPayment(string $reference): array
    {
        try {
            $result = $this->_apiInstance->getInvoiceById($reference);
            return [
                'status' => strtolower($result['status']),
                'raw' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function cancelPayment(string $reference): bool
    {
        try {
            $this->_apiInstance->expireInvoice($reference);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function handleWebhook(array $payload, array $headers): array
    {
        // Xendit usually sends verification token in headers if configured
        // But for simplicity, we process the payload
        return [
            'invoice_number' => $payload['external_id'],
            'status' => strtolower($payload['status']),
            'amount' => $payload['amount'],
            'paid_at' => $payload['paid_at'] ?? null,
            'payment_method' => $payload['payment_method'] ?? null,
        ];
    }
}
