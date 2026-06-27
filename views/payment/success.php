<?php

use app\models\Transaction;
use yii\helpers\Html;

/** @var string|null $invoiceNumber */
/** @var Transaction|null $transaction */

$this->title = 'Pembayaran Berhasil';
$invoice = $invoiceNumber ?: ($transaction->invoice_number ?? null);
$amount = $transaction !== null ? Yii::$app->formatter->asCurrency((float) $transaction->sell_price, 'IDR') : null;
$status = $transaction !== null ? (string) $transaction->status : 'processing';
?>

<section class="payment-result-page">
    <div class="container-xl">
        <div class="payment-result-card payment-result-success">
            <div class="payment-result-icon">
                <i class="bi bi-check-lg" aria-hidden="true"></i>
            </div>

            <div class="payment-result-content">
                <div class="section-kicker">Pembayaran Diterima</div>
                <h1>Pembayaran berhasil</h1>
                <p>Terima kasih. Pesanan kamu sedang diproses otomatis. Simpan nomor invoice untuk mengecek status pesanan.</p>

                <div class="payment-result-details">
                    <div>
                        <span>Invoice</span>
                        <strong><?= Html::encode($invoice ?: '-') ?></strong>
                    </div>
                    <div>
                        <span>Status Order</span>
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
                    <?= Html::a('Kembali ke Beranda', ['/site/index'], ['class' => 'lp-btn lp-btn-ghost']) ?>
                </div>
            </div>
        </div>

        <div class="payment-next-steps">
            <article>
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                <h2>Tunggu proses order</h2>
                <p>Setelah pembayaran masuk, sistem meneruskan pesanan ke supplier.</p>
            </article>
            <article>
                <i class="bi bi-search" aria-hidden="true"></i>
                <h2>Cek dengan invoice</h2>
                <p>Gunakan halaman Track Order untuk melihat status terbaru pesanan kamu.</p>
            </article>
            <article>
                <i class="bi bi-headset" aria-hidden="true"></i>
                <h2>Butuh bantuan?</h2>
                <p>Hubungi kami jika status belum berubah setelah beberapa menit.</p>
            </article>
        </div>
    </div>
</section>
