<?php

declare(strict_types=1);

namespace app\services;

use app\models\Product;
use app\models\Transaction;
use Yii;
use yii\base\Component;
use yii\helpers\Html;
use yii\httpclient\Client;

class ResendEmailService extends Component
{
    public string $apiKey = '';
    public string $fromEmail = '';
    public string $fromName = 'AksesPay';

    private Client $client;

    public function init(): void
    {
        parent::init();

        $this->client = new Client([
            'baseUrl' => 'https://api.resend.com',
        ]);
    }

    public function sendOrderNotification(
        string $toEmail,
        Transaction $transaction,
        Product $product,
        string $paymentUrl,
    ): bool {
        if (trim($this->apiKey) === '') {
            Yii::warning('RESEND_API_KEY is not configured; order email notification skipped.', __METHOD__);
            return false;
        }

        $payload = [
            'from' => $this->formatSender(),
            'to' => [$toEmail],
            'subject' => 'Invoice ' . $transaction->invoice_number . ' - ' . Yii::$app->name,
            'html' => $this->renderOrderHtml($transaction, $product, $paymentUrl),
            'text' => $this->renderOrderText($transaction, $product, $paymentUrl),
        ];

        try {
            $response = $this->client
                ->createRequest()
                ->setMethod('POST')
                ->setUrl('/emails')
                ->addHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->setFormat(Client::FORMAT_JSON)
                ->setData($payload)
                ->send();

            if ($response->isOk) {
                return true;
            }

            Yii::warning([
                'message' => 'Resend order email failed.',
                'statusCode' => $response->statusCode,
                'response' => $response->data,
                'invoiceNumber' => (string) $transaction->invoice_number,
            ], __METHOD__);
        } catch (\Throwable $exception) {
            Yii::error([
                'message' => $exception->getMessage(),
                'invoiceNumber' => (string) $transaction->invoice_number,
            ], __METHOD__);
        }

        return false;
    }

    public function sendPaymentStatusNotification(
        string $toEmail,
        Transaction $transaction,
        ?Product $product,
        string $paymentStatus,
    ): bool {
        if (trim($this->apiKey) === '') {
            Yii::warning('RESEND_API_KEY is not configured; payment status email notification skipped.', __METHOD__);
            return false;
        }

        $status = $this->paymentStatusContent($paymentStatus);
        $payload = [
            'from' => $this->formatSender(),
            'to' => [$toEmail],
            'subject' => $status['subject'] . ' - Invoice ' . $transaction->invoice_number,
            'html' => $this->renderPaymentStatusHtml($transaction, $product, $status),
            'text' => $this->renderPaymentStatusText($transaction, $product, $status),
        ];

        try {
            $response = $this->client
                ->createRequest()
                ->setMethod('POST')
                ->setUrl('/emails')
                ->addHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->setFormat(Client::FORMAT_JSON)
                ->setData($payload)
                ->send();

            if ($response->isOk) {
                return true;
            }

            Yii::warning([
                'message' => 'Resend payment status email failed.',
                'statusCode' => $response->statusCode,
                'response' => $response->data,
                'invoiceNumber' => (string) $transaction->invoice_number,
                'paymentStatus' => $paymentStatus,
            ], __METHOD__);
        } catch (\Throwable $exception) {
            Yii::error([
                'message' => $exception->getMessage(),
                'invoiceNumber' => (string) $transaction->invoice_number,
                'paymentStatus' => $paymentStatus,
            ], __METHOD__);
        }

        return false;
    }

    private function formatSender(): string
    {
        $email = trim($this->fromEmail);
        $name = trim($this->fromName);

        if ($name === '') {
            return $email;
        }

        return $name . ' <' . $email . '>';
    }

    private function renderOrderHtml(Transaction $transaction, Product $product, string $paymentUrl): string
    {
        $invoiceNumber = Html::encode((string) $transaction->invoice_number);
        $productName = Html::encode((string) $product->product_name);
        $target = Html::encode((string) $transaction->target);
        $zone = trim((string) $transaction->zone);
        $amount = Html::encode('Rp' . number_format((float) $transaction->sell_price, 0, ',', '.'));
        $paymentLink = Html::encode($paymentUrl);

        $zoneRow = $zone !== ''
            ? '<tr><td style="padding:8px 0;color:#64748b;">Zone/Server</td><td style="padding:8px 0;text-align:right;">' . Html::encode($zone) . '</td></tr>'
            : '';

        return <<<HTML
<div style="font-family:Arial,sans-serif;color:#0f172a;line-height:1.6;">
    <h1 style="font-size:22px;margin:0 0 12px;">Pesanan berhasil dibuat</h1>
    <p style="margin:0 0 18px;">Invoice <strong>{$invoiceNumber}</strong> sudah dibuat. Silakan selesaikan pembayaran agar pesanan dapat diproses.</p>
    <table style="width:100%;max-width:520px;border-collapse:collapse;margin:0 0 20px;">
        <tr><td style="padding:8px 0;color:#64748b;">Produk</td><td style="padding:8px 0;text-align:right;">{$productName}</td></tr>
        <tr><td style="padding:8px 0;color:#64748b;">User ID</td><td style="padding:8px 0;text-align:right;">{$target}</td></tr>
        {$zoneRow}
        <tr><td style="padding:8px 0;color:#64748b;">Total Bayar</td><td style="padding:8px 0;text-align:right;"><strong>{$amount}</strong></td></tr>
    </table>
    <p style="margin:0 0 18px;"><a href="{$paymentLink}" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 16px;border-radius:8px;text-decoration:none;font-weight:700;">Bayar Sekarang</a></p>
    <p style="margin:0;color:#64748b;font-size:13px;">Simpan nomor invoice untuk mengecek status pesanan melalui halaman Track Order.</p>
</div>
HTML;
    }

    private function renderOrderText(Transaction $transaction, Product $product, string $paymentUrl): string
    {
        $lines = [
            'Pesanan berhasil dibuat',
            '',
            'Invoice: ' . $transaction->invoice_number,
            'Produk: ' . $product->product_name,
            'User ID: ' . $transaction->target,
        ];

        if (trim((string) $transaction->zone) !== '') {
            $lines[] = 'Zone/Server: ' . $transaction->zone;
        }

        $lines[] = 'Total Bayar: Rp' . number_format((float) $transaction->sell_price, 0, ',', '.');
        $lines[] = '';
        $lines[] = 'Bayar sekarang: ' . $paymentUrl;
        $lines[] = 'Simpan nomor invoice untuk mengecek status pesanan melalui halaman Track Order.';

        return implode("\n", $lines);
    }

    private function renderPaymentStatusHtml(Transaction $transaction, ?Product $product, array $status): string
    {
        $invoiceNumber = Html::encode((string) $transaction->invoice_number);
        $productName = Html::encode($product !== null ? (string) $product->product_name : 'Produk digital');
        $target = Html::encode((string) $transaction->target);
        $zone = trim((string) $transaction->zone);
        $amount = Html::encode('Rp' . number_format((float) $transaction->sell_price, 0, ',', '.'));
        $title = Html::encode($status['title']);
        $message = Html::encode($status['message']);

        $zoneRow = $zone !== ''
            ? '<tr><td style="padding:8px 0;color:#64748b;">Zone/Server</td><td style="padding:8px 0;text-align:right;">' . Html::encode($zone) . '</td></tr>'
            : '';

        return <<<HTML
<div style="font-family:Arial,sans-serif;color:#0f172a;line-height:1.6;">
    <h1 style="font-size:22px;margin:0 0 12px;">{$title}</h1>
    <p style="margin:0 0 18px;">{$message}</p>
    <table style="width:100%;max-width:520px;border-collapse:collapse;margin:0 0 20px;">
        <tr><td style="padding:8px 0;color:#64748b;">Invoice</td><td style="padding:8px 0;text-align:right;"><strong>{$invoiceNumber}</strong></td></tr>
        <tr><td style="padding:8px 0;color:#64748b;">Produk</td><td style="padding:8px 0;text-align:right;">{$productName}</td></tr>
        <tr><td style="padding:8px 0;color:#64748b;">User ID</td><td style="padding:8px 0;text-align:right;">{$target}</td></tr>
        {$zoneRow}
        <tr><td style="padding:8px 0;color:#64748b;">Total Bayar</td><td style="padding:8px 0;text-align:right;"><strong>{$amount}</strong></td></tr>
    </table>
    <p style="margin:0;color:#64748b;font-size:13px;">Simpan nomor invoice untuk bantuan dan pengecekan melalui halaman Track Order.</p>
</div>
HTML;
    }

    private function renderPaymentStatusText(Transaction $transaction, ?Product $product, array $status): string
    {
        $lines = [
            $status['title'],
            '',
            $status['message'],
            '',
            'Invoice: ' . $transaction->invoice_number,
            'Produk: ' . ($product !== null ? $product->product_name : 'Produk digital'),
            'User ID: ' . $transaction->target,
        ];

        if (trim((string) $transaction->zone) !== '') {
            $lines[] = 'Zone/Server: ' . $transaction->zone;
        }

        $lines[] = 'Total Bayar: Rp' . number_format((float) $transaction->sell_price, 0, ',', '.');
        $lines[] = '';
        $lines[] = 'Simpan nomor invoice untuk bantuan dan pengecekan melalui halaman Track Order.';

        return implode("\n", $lines);
    }

    private function paymentStatusContent(string $paymentStatus): array
    {
        return match ($paymentStatus) {
            'paid', 'settled', 'success' => [
                'subject' => 'Pembayaran berhasil',
                'title' => 'Pembayaran berhasil',
                'message' => 'Pembayaran kamu sudah kami terima. Pesanan sedang diproses otomatis.',
            ],
            'expired' => [
                'subject' => 'Pembayaran expired',
                'title' => 'Pembayaran expired',
                'message' => 'Batas waktu pembayaran invoice ini sudah berakhir. Silakan buat pesanan baru jika masih ingin melanjutkan.',
            ],
            'cancelled' => [
                'subject' => 'Pembayaran dibatalkan',
                'title' => 'Pembayaran dibatalkan',
                'message' => 'Pembayaran invoice ini dibatalkan. Kamu bisa membuat pesanan baru dari halaman produk.',
            ],
            default => [
                'subject' => 'Pembayaran gagal',
                'title' => 'Pembayaran gagal',
                'message' => 'Pembayaran invoice ini belum berhasil. Jika dana sudah terpotong, hubungi support dengan nomor invoice.',
            ],
        };
    }
}
