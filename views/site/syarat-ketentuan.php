<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Syarat & Ketentuan';
$this->params['meta_description'] = 'Syarat dan ketentuan penggunaan layanan AksesPay untuk pembelian produk digital, pembayaran, refund, dan tanggung jawab pengguna.';
$this->params['meta_keywords'] = 'syarat ketentuan aksespay, aturan topup, refund, transaksi digital';

$sections = [
    [
        'title' => '1. Ketentuan Umum',
        'items' => [
            'Dengan mengakses atau menggunakan layanan AksesPay, pengguna dianggap telah membaca, memahami, dan menyetujui seluruh Syarat & Ketentuan ini.',
            'AksesPay dapat mengubah ketentuan ini sewaktu-waktu untuk menyesuaikan perkembangan layanan, kebijakan mitra, atau peraturan yang berlaku.',
            'Penggunaan layanan setelah perubahan dipublikasikan dianggap sebagai persetujuan pengguna atas ketentuan terbaru.',
        ],
    ],
    [
        'title' => '2. Definisi',
        'items' => [
            'AksesPay adalah platform yang menyediakan layanan pembelian produk digital seperti topup game, voucher, PPOB, dan layanan digital lain.',
            'Pengguna adalah setiap pihak yang mengakses, melihat, membeli, atau menggunakan layanan AksesPay.',
            'Pembeli adalah pengguna yang melakukan transaksi melalui sistem AksesPay, baik sebagai tamu maupun member.',
            'Invoice adalah nomor referensi transaksi yang diterbitkan oleh sistem AksesPay untuk kebutuhan pembayaran dan pelacakan pesanan.',
        ],
    ],
    [
        'title' => '3. Tanggung Jawab Pengguna',
        'items' => [
            'Pengguna wajib mengisi data transaksi secara benar, termasuk User ID, server, nomor tujuan, email, dan informasi lain yang diminta.',
            'Kesalahan input data oleh pengguna dapat menyebabkan produk terkirim ke tujuan yang salah dan bukan menjadi tanggung jawab AksesPay.',
            'Pengguna dilarang menggunakan layanan untuk aktivitas yang melanggar hukum, merugikan pihak lain, atau mengganggu sistem AksesPay.',
            'Pengguna wajib menjaga keamanan akun, invoice, dan informasi transaksi pribadi.',
        ],
    ],
    [
        'title' => '4. Transaksi dan Pembayaran',
        'items' => [
            'Setiap transaksi wajib dilakukan melalui sistem resmi AksesPay dan mengikuti instruksi pembayaran yang tampil di halaman checkout.',
            'Harga, biaya admin, metode pembayaran, dan ketersediaan produk dapat berubah sewaktu-waktu mengikuti kondisi supplier dan payment gateway.',
            'Transaksi yang dilakukan di luar sistem AksesPay tidak menjadi tanggung jawab AksesPay.',
            'Pesanan akan diproses setelah pembayaran berhasil dikonfirmasi oleh sistem atau payment gateway.',
        ],
    ],
    [
        'title' => '5. Pemrosesan Pesanan',
        'items' => [
            'Sebagian besar pesanan diproses otomatis setelah pembayaran diterima, namun beberapa kondisi dapat memerlukan waktu lebih lama.',
            'Keterlambatan dapat terjadi karena gangguan supplier, maintenance game, gangguan jaringan, validasi pembayaran, atau kendala teknis lain.',
            'Pengguna dapat memantau status pesanan melalui halaman Track Order menggunakan nomor invoice.',
        ],
    ],
    [
        'title' => '6. Refund dan Pembatalan',
        'items' => [
            'Refund dapat dipertimbangkan apabila pesanan gagal diproses dan produk belum diterima oleh pengguna.',
            'Refund tidak berlaku untuk transaksi yang sudah berhasil dikirim ke data tujuan yang diinput pengguna.',
            'Proses refund dapat memerlukan verifikasi invoice, bukti pembayaran, metode pembayaran, dan data pendukung lain.',
            'Waktu pengembalian dana dapat berbeda mengikuti kebijakan payment gateway, bank, atau penyedia metode pembayaran.',
        ],
    ],
    [
        'title' => '7. Data dan Privasi',
        'items' => [
            'AksesPay mengumpulkan dan memproses data transaksi untuk menjalankan layanan, memverifikasi pembayaran, memproses pesanan, dan memberikan bantuan pelanggan.',
            'Pengelolaan data pribadi dijelaskan lebih lanjut dalam Kebijakan Privasi AksesPay.',
        ],
    ],
    [
        'title' => '8. Pembatasan Layanan',
        'items' => [
            'AksesPay berhak menolak, menunda, membatalkan, atau membatasi transaksi apabila ditemukan indikasi penyalahgunaan, fraud, pelanggaran hukum, atau pelanggaran ketentuan ini.',
            'AksesPay tidak bertanggung jawab atas kerugian tidak langsung, kehilangan peluang bisnis, atau gangguan pihak ketiga di luar kendali wajar AksesPay.',
        ],
    ],
];
?>

<section class="legal-page">
    <div class="container-xl">
        <div class="legal-hero">
            <div>
                <div class="section-kicker">Legal AksesPay</div>
                <h1>Syarat & Ketentuan</h1>
                <p>Ketentuan ini mengatur penggunaan layanan AksesPay, termasuk transaksi, pembayaran, pemrosesan pesanan, refund, dan tanggung jawab pengguna.</p>
            </div>
            <span>Update: <?= Html::encode(date('d M Y')) ?></span>
        </div>

        <div class="legal-layout">
            <aside class="legal-sidebar">
                <strong>Ringkasan</strong>
                <a href="#legal-section-1">Ketentuan Umum</a>
                <a href="#legal-section-3">Tanggung Jawab</a>
                <a href="#legal-section-4">Transaksi</a>
                <a href="#legal-section-6">Refund</a>
                <?= Html::a('Kebijakan Privasi', ['/site/kebijakan-privasi']) ?>
            </aside>

            <div class="legal-content">
                <?php foreach ($sections as $index => $section): ?>
                    <article id="legal-section-<?= Html::encode((string) ($index + 1)) ?>" class="legal-section">
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
