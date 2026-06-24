<?php

namespace app\interfaces;

interface PaymentGatewayInterface
{
    public function createInvoice(string $invoiceNumber, float $amount, string $paymentMethod, array $customerDetails): array;

    public function getPaymentStatus(string $reference): array;

    public function cancelInvoice(string $reference): bool;

    public function handleWebhook(array $payload, array $headers): array;
}
