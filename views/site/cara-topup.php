<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Cara Topup';
$this->params['meta_description'] = 'Panduan cara topup di AksesPay mulai dari pilih kategori, isi data, pembayaran, hingga cek status order.';
$this->params['meta_keywords'] = 'cara topup, panduan topup, aksespay, cek invoice, pembayaran qris';

$steps = [
    ['bi-grid-3x3-gap', 'Pilih Kategori', 'Buka kategori produk, lalu pilih game, voucher, PPOB, atau layanan digital yang kamu butuhkan.'],
    ['bi-bag-check', 'Pilih Produk', 'Pilih nominal atau paket yang tersedia. Pastikan nama produk dan harga sudah sesuai sebelum lanjut.'],
    ['bi-person-vcard', 'Isi Data Tujuan', 'Masukkan User ID, zona/server jika diminta, dan email aktif untuk menerima informasi pesanan.'],
    ['bi-wallet2', 'Bayar Pesanan', 'Klik topup, lalu selesaikan pembayaran melalui metode yang tersedia seperti QRIS atau virtual account.'],
    ['bi-search', 'Track Order', 'Gunakan nomor invoice untuk melihat status pembayaran dan proses pengiriman produk secara mandiri.'],
];

$notes = [
    'Pastikan User ID dan server sudah benar sebelum melakukan pembayaran.',
    'Simpan nomor invoice sampai pesanan selesai.',
    'Jika pembayaran berhasil tetapi status belum berubah, tunggu beberapa menit lalu cek ulang di Track Order.',
];
?>

<section class="howtopup-page">
    <div class="container-xl">
        <div class="howtopup-hero">
            <div>
                <div class="section-kicker">Panduan AksesPay</div>
                <h1>Cara topup di AksesPay</h1>
                <p>Ikuti langkah singkat ini untuk membeli produk digital dengan aman, cepat, dan mudah dilacak.</p>
            </div>
            <?= Html::a('Mulai Topup', ['/site/categories'], ['class' => 'lp-btn lp-btn-primary']) ?>
        </div>

        <div class="howtopup-steps">
            <?php foreach ($steps as $index => $step): ?>
                <article class="howtopup-step">
                    <div class="howtopup-step-icon">
                        <i class="bi <?= Html::encode($step[0]) ?>" aria-hidden="true"></i>
                    </div>
                    <span>Langkah <?= Html::encode((string) ($index + 1)) ?></span>
                    <h2><?= Html::encode($step[1]) ?></h2>
                    <p><?= Html::encode($step[2]) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="howtopup-info-grid">
            <section class="howtopup-panel">
                <div class="section-kicker">Setelah Pembayaran</div>
                <h2>Cek status dengan invoice</h2>
                <p>Setelah pembayaran berhasil, sistem akan memproses order. Kamu bisa melihat perkembangan status melalui halaman Track Order.</p>
                <?= Html::a('Track Order', ['/site/track-order'], ['class' => 'lp-btn lp-btn-ghost']) ?>
            </section>

            <section class="howtopup-panel">
                <div class="section-kicker">Catatan Penting</div>
                <ul class="howtopup-note-list">
                    <?php foreach ($notes as $note): ?>
                        <li><i class="bi bi-check2-circle" aria-hidden="true"></i><span><?= Html::encode($note) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>
    </div>
</section>
