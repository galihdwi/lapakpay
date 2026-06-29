<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Kebijakan Pengembalian Dana (Refund Policy)';
$this->params['meta_description'] = 'Kebijakan pengembalian dana AksesPay untuk transaksi produk digital, verifikasi pesanan, dan estimasi proses refund.';
$this->params['meta_keywords'] = 'refund policy aksespay, kebijakan pengembalian dana, refund topup, pembatalan transaksi';

$sections = [
    [
        'title' => '1. Ketentuan Refund',
        'items' => [
            'Refund dapat dipertimbangkan apabila pembayaran berhasil tetapi pesanan gagal diproses dan produk belum diterima oleh pengguna.',
            'Refund tidak berlaku untuk transaksi yang sudah berhasil dikirim ke data tujuan yang diinput pengguna.',
            'Kesalahan input data seperti User ID, server, nomor tujuan, atau informasi akun bukan menjadi tanggung jawab AksesPay.',
        ],
    ],
    [
        'title' => '2. Proses Verifikasi',
        'items' => [
            'Pengguna wajib menyampaikan nomor invoice, bukti pembayaran, metode pembayaran, dan informasi pendukung lain yang diminta tim AksesPay.',
            'Tim AksesPay akan memeriksa status pembayaran, status supplier, dan riwayat pemrosesan pesanan sebelum keputusan refund diberikan.',
        ],
    ],
    [
        'title' => '3. Waktu Pengembalian Dana',
        'items' => [
            'Waktu pengembalian dana dapat berbeda mengikuti kebijakan payment gateway, bank, atau penyedia metode pembayaran.',
            'Refund akan dikirimkan ke metode pembayaran atau rekening yang disetujui setelah proses verifikasi selesai.',
        ],
    ],
    [
        'title' => '4. Pembatalan Transaksi',
        'items' => [
            'Transaksi yang belum dibayar dapat diabaikan sampai melewati batas waktu pembayaran.',
            'Transaksi yang sudah dibayar tidak dapat dibatalkan sepihak apabila pesanan sudah masuk proses pengiriman atau berhasil terkirim.',
        ],
    ],
];
?>

<section class="legal-page">
    <div class="container-xl">
        <div class="legal-hero">
            <div>
                <div class="section-kicker">Refund AksesPay</div>
                <h1>Kebijakan Pengembalian Dana (Refund Policy)</h1>
                <p>Kebijakan ini menjelaskan kondisi, verifikasi, dan estimasi proses pengembalian dana untuk transaksi di AksesPay.</p>
            </div>
            <span>Update: <?= Html::encode(date('d M Y')) ?></span>
        </div>

        <div class="legal-layout">
            <aside class="legal-sidebar">
                <strong>Ringkasan</strong>
                <a href="#refund-section-1">Ketentuan Refund</a>
                <a href="#refund-section-2">Proses Verifikasi</a>
                <a href="#refund-section-3">Waktu Refund</a>
                <a href="#refund-section-4">Pembatalan</a>
                <?= Html::a('Syarat & Ketentuan', ['/site/syarat-ketentuan']) ?>
                <?= Html::a('FAQ', ['/site/faq']) ?>
            </aside>

            <div class="legal-content">
                <?php foreach ($sections as $index => $section): ?>
                    <article id="refund-section-<?= Html::encode((string) ($index + 1)) ?>" class="legal-section">
                        <h2><?= Html::encode($section['title']) ?></h2>
                        <ul>
                            <?php foreach ($section['items'] as $item): ?>
                                <li><?= Html::encode($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
