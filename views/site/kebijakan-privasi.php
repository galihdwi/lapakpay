<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Kebijakan Privasi';
$this->params['meta_description'] = 'Kebijakan Privasi AksesPay mengenai pengumpulan, penggunaan, penyimpanan, pembagian, dan perlindungan data pribadi pengguna.';
$this->params['meta_keywords'] = 'kebijakan privasi aksespay, data pribadi, perlindungan data, privasi topup';

$sections = [
    [
        'title' => '1. Pengumpulan Data Pribadi',
        'items' => [
            'AksesPay dapat mengumpulkan data yang pengguna berikan saat melakukan transaksi, seperti email, nomor invoice, data tujuan topup, dan informasi lain yang dibutuhkan untuk memproses pesanan.',
            'AksesPay dapat menyimpan data transaksi, termasuk produk yang dibeli, nominal pembayaran, metode pembayaran, status pesanan, waktu transaksi, dan data teknis terkait.',
            'AksesPay dapat mengumpulkan informasi perangkat dan penggunaan secara terbatas, seperti alamat IP, browser, waktu akses, dan aktivitas yang diperlukan untuk keamanan serta peningkatan layanan.',
        ],
    ],
    [
        'title' => '2. Penggunaan Data Pribadi',
        'items' => [
            'Data digunakan untuk memproses pembayaran, meneruskan pesanan ke supplier, menampilkan status pesanan, dan memberikan bantuan pelanggan.',
            'Data dapat digunakan untuk mencegah penyalahgunaan layanan, mendeteksi transaksi mencurigakan, menjaga keamanan sistem, dan memenuhi kewajiban hukum yang berlaku.',
            'AksesPay dapat menggunakan data transaksi secara agregat untuk analisis performa, perbaikan layanan, dan pengembangan fitur.',
        ],
    ],
    [
        'title' => '3. Pembagian Data',
        'items' => [
            'AksesPay hanya membagikan data kepada pihak yang diperlukan untuk menjalankan layanan, seperti payment gateway, supplier produk digital, penyedia hosting, dan layanan pendukung operasional.',
            'Pihak ketiga yang menerima data hanya boleh memproses data sesuai kebutuhan layanan dan kewajiban hukum yang berlaku.',
            'AksesPay dapat mengungkapkan data apabila diwajibkan oleh hukum, perintah pengadilan, regulator, atau otoritas yang berwenang.',
        ],
    ],
    [
        'title' => '4. Penyimpanan Data',
        'items' => [
            'Data pribadi dan transaksi disimpan selama diperlukan untuk menyediakan layanan, menyelesaikan sengketa, memenuhi kewajiban hukum, audit, dan kebutuhan keamanan.',
            'Apabila data tidak lagi diperlukan, AksesPay dapat menghapus, menganonimkan, atau membatasi pemrosesan data sesuai prosedur internal dan ketentuan yang berlaku.',
        ],
    ],
    [
        'title' => '5. Keamanan Data',
        'items' => [
            'AksesPay menerapkan langkah keamanan yang wajar untuk melindungi data dari akses tidak sah, perubahan, kehilangan, penyalahgunaan, atau pengungkapan yang tidak sah.',
            'Tidak ada sistem elektronik yang sepenuhnya bebas risiko. Pengguna disarankan menjaga keamanan email, akun, invoice, dan bukti transaksi pribadi.',
        ],
    ],
    [
        'title' => '6. Hak Pengguna',
        'items' => [
            'Pengguna dapat meminta akses, koreksi, pembaruan, atau penghapusan data pribadi sesuai ketentuan hukum yang berlaku.',
            'Pengguna dapat menghubungi AksesPay apabila ingin menanyakan pemrosesan data pribadi atau menyampaikan keberatan yang sah.',
            'Permintaan tertentu dapat memerlukan verifikasi identitas dan bukti kepemilikan transaksi untuk melindungi data pengguna.',
        ],
    ],
    [
        'title' => '7. Tautan dan Layanan Pihak Ketiga',
        'items' => [
            'Layanan AksesPay dapat memuat tautan atau integrasi ke pihak ketiga. Kebijakan privasi pihak ketiga tersebut berada di luar kendali AksesPay.',
            'Pengguna disarankan membaca kebijakan privasi dan ketentuan layanan pihak ketiga sebelum menggunakan layanan mereka.',
        ],
    ],
    [
        'title' => '8. Perubahan Kebijakan',
        'items' => [
            'AksesPay dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu untuk menyesuaikan perubahan layanan, teknologi, atau peraturan.',
            'Penggunaan layanan setelah kebijakan diperbarui dianggap sebagai persetujuan atas perubahan tersebut.',
        ],
    ],
    [
        'title' => '9. Hubungi Kami',
        'items' => [
            'Jika pengguna memiliki pertanyaan terkait kebijakan ini atau permintaan terkait data pribadi, pengguna dapat menghubungi AksesPay melalui halaman Kontak.',
            'AksesPay dapat memverifikasi identitas pengguna sebelum menanggapi permintaan terkait data pribadi.',
        ],
    ],
];
?>

<section class="legal-page">
    <div class="container-xl">
        <div class="legal-hero">
            <div>
                <div class="section-kicker">Privasi AksesPay</div>
                <h1>Kebijakan Privasi</h1>
                <p>Kebijakan ini menjelaskan bagaimana AksesPay mengumpulkan, menggunakan, menyimpan, membagikan, dan melindungi data pribadi pengguna.</p>
            </div>
            <span>Efektif: <?= Html::encode(date('d M Y')) ?></span>
        </div>

        <div class="legal-layout">
            <aside class="legal-sidebar">
                <strong>Ringkasan</strong>
                <a href="#privacy-section-1">Pengumpulan Data</a>
                <a href="#privacy-section-2">Penggunaan Data</a>
                <a href="#privacy-section-3">Pembagian Data</a>
                <a href="#privacy-section-6">Hak Pengguna</a>
                <?= Html::a('Syarat & Ketentuan', ['/site/syarat-ketentuan']) ?>
            </aside>

            <div class="legal-content">
                <?php foreach ($sections as $index => $section): ?>
                    <article id="privacy-section-<?= Html::encode((string) ($index + 1)) ?>" class="legal-section">
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
