<?php

/** @var yii\web\View $this */
/** @var string $invoice */
/** @var app\models\Transaction|null $transaction */
/** @var app\models\Product|null $product */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Track Order - AksesPay';
$this->params['breadcrumbs'][] = 'Track Order';

$statusLabels = [
    'pending' => 'Menunggu Pembayaran',
    'processing' => 'Diproses',
    'success' => 'Berhasil',
    'paid' => 'Dibayar',
    'failed' => 'Gagal',
    'error' => 'Bermasalah',
    'cancelled' => 'Dibatalkan',
    'expired' => 'Kedaluwarsa',
];

$status = strtolower((string) ($transaction?->status ?? ''));
$statusLabel = $statusLabels[$status] ?? ($status !== '' ? ucfirst($status) : '-');
$statusTone = match ($status) {
    'success', 'paid' => 'success',
    'processing' => 'process',
    'failed', 'error', 'cancelled', 'expired' => 'danger',
    default => 'pending',
};
$target = trim((string) ($transaction?->target ?? '') . (($transaction?->zone ?? '') !== '' ? ' / ' . $transaction->zone : ''));
?>

<section class="content-section track-order-page">
    <div class="container-xl">
        <div class="section-head">
            <div>
                <div class="section-kicker">Pelacakan Pesanan</div>
                <h1 class="section-title">Track Order</h1>
                <p class="lp-muted mb-0">Masukkan nomor invoice untuk mengecek status transaksi.</p>
            </div>
        </div>

        <div class="track-order-layout">
            <div class="track-main">
                <div class="track-card track-search-card">
                    <div class="track-card-head">
                        <span class="step-label">1</span>
                        <div>
                            <h2>Cek Nomor Invoice</h2>
                            <p class="lp-muted mb-0">Gunakan nomor invoice dari checkout untuk melihat status terbaru.</p>
                        </div>
                    </div>

                    <?= Html::beginForm(['/site/track-order'], 'get', ['class' => 'track-form']) ?>
                        <?= Html::textInput('invoice', $invoice, [
                            'class' => 'form-control lp-input',
                            'placeholder' => 'Contoh: INV260626143012123',
                            'autocomplete' => 'off',
                            'inputmode' => 'text',
                        ]) ?>
                        <?= Html::submitButton('Cek Order', ['class' => 'lp-btn lp-btn-primary']) ?>
                    <?= Html::endForm() ?>
                </div>

                <?php if ($invoice !== '' && $transaction === null): ?>
                    <div class="track-card track-empty-state">
                        <strong>Invoice tidak ditemukan</strong>
                        <p class="lp-muted mb-0">Invoice <?= Html::encode($invoice) ?> belum ada di sistem. Pastikan nomor invoice sudah benar.</p>
                    </div>
                <?php endif; ?>

                <?php if ($transaction !== null): ?>
                    <div class="track-card track-result-card">
                        <div class="track-result-head">
                            <div>
                                <span class="track-eyebrow">Invoice</span>
                                <h2><?= Html::encode((string) $transaction->invoice_number) ?></h2>
                            </div>
                            <span class="track-status track-status-<?= Html::encode($statusTone) ?>"><?= Html::encode($statusLabel) ?></span>
                        </div>

                        <div class="track-total">
                            <span>Total Pembayaran</span>
                            <strong>Rp<?= Html::encode(number_format((float) $transaction->sell_price, 0, ',', '.')) ?></strong>
                        </div>

                        <div class="track-detail-grid">
                            <div class="track-detail-item">
                                <span>Produk</span>
                                <strong><?= Html::encode((string) ($product?->product_name ?? $transaction->product_id)) ?></strong>
                            </div>
                            <div class="track-detail-item">
                                <span>Tujuan</span>
                                <strong><?= Html::encode($target !== '' ? $target : '-') ?></strong>
                            </div>
                            <div class="track-detail-item">
                                <span>Gateway</span>
                                <strong><?= Html::encode((string) ($transaction->payment_gateway ?: $transaction->payment_method ?: '-')) ?></strong>
                            </div>
                            <div class="track-detail-item">
                                <span>Tanggal</span>
                                <strong><?= Html::encode((string) ($transaction->created_at ?: '-')) ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="track-card track-help-card">
                <h2>Bantuan</h2>
                <p class="lp-muted">Nomor invoice muncul setelah checkout berhasil dibuat. Simpan nomor tersebut untuk memantau pembayaran dan proses pesanan.</p>
                <div class="track-help-list">
                    <span>Pending: menunggu pembayaran</span>
                    <span>Processing: pesanan sedang diproses</span>
                    <span>Success: pesanan selesai</span>
                </div>
                <a class="lp-btn lp-btn-ghost w-100" href="<?= Html::encode(Url::to(['/site/index'])) ?>">Kembali ke Home</a>
            </aside>
        </div>
    </div>
</section>
