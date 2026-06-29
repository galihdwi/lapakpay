<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'FAQ';
$this->params['meta_description'] = 'FAQ AksesPay berisi jawaban tentang cara topup, pembayaran, status pesanan, invoice, dan refund.';
$this->params['meta_keywords'] = 'faq aksespay, pertanyaan topup, bantuan pembayaran, status pesanan';

$sections = [
    [
        'title' => '1. Bagaimana cara melakukan topup?',
        'items' => [
            'Pilih kategori atau produk, isi data tujuan dengan benar, lalu selesaikan pembayaran melalui metode yang tersedia.',
            'Setelah pembayaran berhasil, pesanan akan diproses otomatis atau sesuai antrean supplier.',
        ],
    ],
    [
        'title' => '2. Di mana saya bisa mengecek status pesanan?',
        'items' => [
            'Gunakan halaman Track Order dengan memasukkan nomor invoice transaksi.',
            'Simpan nomor invoice sampai pesanan selesai diproses.',
        ],
    ],
    [
        'title' => '3. Mengapa pesanan belum masuk?',
        'items' => [
            'Keterlambatan dapat terjadi karena gangguan supplier, maintenance game, validasi pembayaran, atau kendala jaringan.',
            'Jika pembayaran berhasil tetapi status belum berubah, tunggu beberapa menit lalu cek ulang melalui Track Order.',
        ],
    ],
    [
        'title' => '4. Apakah transaksi bisa di-refund?',
        'items' => [
            'Refund dapat dipertimbangkan apabila pesanan gagal diproses dan produk belum diterima oleh pengguna.',
            'Transaksi yang sudah berhasil terkirim ke data tujuan yang diinput pengguna tidak dapat di-refund.',
        ],
    ],
    [
        'title' => '5. Bagaimana menghubungi bantuan?',
        'items' => [
            'Hubungi tim AksesPay melalui halaman Kontak dengan menyertakan nomor invoice dan bukti pembayaran jika diperlukan.',
        ],
    ],
];
?>

<section class="legal-page">
    <div class="container-xl">
        <div class="legal-hero">
            <div>
                <div class="section-kicker">Bantuan AksesPay</div>
                <h1>FAQ</h1>
                <p>Jawaban singkat untuk pertanyaan umum seputar topup, pembayaran, status pesanan, dan refund.</p>
            </div>
            <?= Html::a('Track Order', ['/site/track-order'], ['class' => 'lp-btn lp-btn-primary']) ?>
        </div>

        <div class="legal-layout">
            <aside class="legal-sidebar">
                <strong>Ringkasan</strong>
                <a href="#faq-section-1">Cara Topup</a>
                <a href="#faq-section-2">Status Pesanan</a>
                <a href="#faq-section-3">Pesanan Tertunda</a>
                <a href="#faq-section-4">Refund</a>
                <?= Html::a('Syarat & Ketentuan', ['/site/syarat-ketentuan']) ?>
                <?= Html::a('Kebijakan Pengembalian Dana (Refund Policy)', ['/site/kebijakan-pengembalian-dana']) ?>
            </aside>

            <div class="legal-content">
                <?php foreach ($sections as $index => $section): ?>
                    <article id="faq-section-<?= Html::encode((string) ($index + 1)) ?>" class="legal-section">
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
