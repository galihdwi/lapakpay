<?php

use app\models\Transaction;
use yii\helpers\Html;

/** @var string|null $invoiceNumber */
/** @var Transaction|null $transaction */

$status = strtolower((string) ($transaction->status ?? 'pending'));
$invoice = $invoiceNumber ?: ($transaction->invoice_number ?? null);
$amount = $transaction !== null ? Yii::$app->formatter->asCurrency((float) $transaction->sell_price, 'IDR') : null;

$states = [
    'expired' => [
        'title' => 'Pembayaran expired',
        'kicker' => 'Waktu Pembayaran Habis',
        'message' => 'Invoice ini sudah melewati batas waktu pembayaran. Silakan buat pesanan baru jika masih ingin melanjutkan.',
        'icon' => 'bi-hourglass-bottom',
        'class' => 'payment-result-expired',
    ],
    'failed' => [
        'title' => 'Pembayaran gagal',
        'kicker' => 'Pembayaran Tidak Berhasil',
        'message' => 'Pembayaran belum berhasil dikonfirmasi. Kamu bisa membuat pesanan baru atau menghubungi bantuan jika dana sudah terpotong.',
        'icon' => 'bi-x-lg',
        'class' => 'payment-result-failed',
    ],
    'cancelled' => [
        'title' => 'Pembayaran dibatalkan',
        'kicker' => 'Checkout Dibatalkan',
        'message' => 'Pembayaran untuk invoice ini dibatalkan. Kamu bisa kembali memilih kategori dan membuat pesanan baru.',
        'icon' => 'bi-slash-circle',
        'class' => 'payment-result-failed',
    ],
    'pending' => [
        'title' => 'Menunggu pembayaran',
        'kicker' => 'Pembayaran Pending',
        'message' => 'Pembayaran belum dikonfirmasi. Jika kamu baru saja membayar, tunggu beberapa menit lalu cek status dengan nomor invoice.',
        'icon' => 'bi-clock-history',
        'class' => 'payment-result-pending',
    ],
];

$state = $states[$status] ?? $states['pending'];
$this->title = ucwords($state['title']);
?>

<section class="payment-result-page">
    <div class="container-xl">
        <div class="payment-result-card <?= Html::encode($state['class']) ?>">
            <div class="payment-result-icon">
                <i class="bi <?= Html::encode($state['icon']) ?>" aria-hidden="true"></i>
            </div>

            <div class="payment-result-content">
                <div class="section-kicker"><?= Html::encode($state['kicker']) ?></div>
                <h1><?= Html::encode($state['title']) ?></h1>
                <p><?= Html::encode($state['message']) ?></p>

                <div class="payment-result-details">
                    <div>
                        <span>Invoice</span>
                        <strong><?= Html::encode($invoice ?: '-') ?></strong>
                    </div>
                    <div>
                        <span>Status</span>
                        <strong><?= Html::encode(ucwords(str_replace('_', ' ', $status))) ?></strong>
                    </div>
                    <?php if ($amount !== null): ?>
                        <div>
                            <span>Total Pembayaran</span>
                            <strong><?= Html::encode($amount) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="payment-result-actions">
                    <?= Html::a('Track Order', ['/site/track-order', 'invoice' => $invoice], ['class' => 'lp-btn lp-btn-primary']) ?>
                    <?= Html::a('Buat Pesanan Baru', ['/site/categories'], ['class' => 'lp-btn lp-btn-ghost']) ?>
                    <?= Html::a('Kontak Bantuan', ['/site/contact'], ['class' => 'lp-btn lp-btn-ghost']) ?>
                </div>
            </div>
        </div>

        <div class="payment-next-steps">
            <article>
                <i class="bi bi-receipt" aria-hidden="true"></i>
                <h2>Simpan invoice</h2>
                <p>Nomor invoice membantu kami mengecek status pembayaran dan order kamu.</p>
            </article>
            <article>
                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                <h2>Coba lagi</h2>
                <p>Jika invoice expired atau gagal, buat pesanan baru dari halaman kategori.</p>
            </article>
            <article>
                <i class="bi bi-headset" aria-hidden="true"></i>
                <h2>Hubungi bantuan</h2>
                <p>Jika dana sudah terpotong, kirim invoice dan bukti pembayaran melalui kontak.</p>
            </article>
        </div>
    </div>
</section>
