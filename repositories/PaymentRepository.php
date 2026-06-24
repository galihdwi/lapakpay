<?php

namespace app\repositories;

use app\models\Payment;

class PaymentRepository
{
    public function findByInvoiceNumber(string $invoiceNumber): ?Payment
    {
        return Payment::findOne(['invoice_number' => $invoiceNumber]);
    }

    public function findByGatewayReference(?string $gatewayReference): ?Payment
    {
        if ($gatewayReference === null || $gatewayReference === '') {
            return null;
        }

        return Payment::findOne(['gateway_reference' => $gatewayReference]);
    }

    public function getOrCreateByInvoiceNumber(string $invoiceNumber): Payment
    {
        return $this->findByInvoiceNumber($invoiceNumber) ?: new Payment(['invoice_number' => $invoiceNumber]);
    }

    public function save(Payment $payment, bool $runValidation = true, ?array $attributes = null): bool
    {
        return $payment->save($runValidation, $attributes);
    }
}
