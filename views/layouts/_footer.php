<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;

?>
<footer id="footer" class="lp-footer">
    <div class="container-xl">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="brand-mark">AP</span>
                    <strong class="text-white">AksesPay</strong>
                </div>
                <p class="lp-muted mb-3">Topup game, PPOB, dan streaming premium dengan proses otomatis, pembayaran aman, dan dukungan reseller.</p>
                <p class="lp-footer-address mb-3">Jl. Gadang VI No 6 Kec. Sukun Kel Gadang Malang, Jawa Timur 65149</p>
                <div class="lp-social-row">
                    <a href="#" class="lp-social" aria-label="Instagram"><i class="bi bi-instagram" aria-hidden="true"></i></a>
                    <a href="#" class="lp-social" aria-label="TikTok"><i class="bi bi-music-note-beamed" aria-hidden="true"></i></a>
                    <a href="#" class="lp-social" aria-label="X"><i class="bi bi-twitter-x" aria-hidden="true"></i></a>
                    <a href="#" class="lp-social" aria-label="YouTube"><i class="bi bi-youtube" aria-hidden="true"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h3 class="lp-footer-title">Kategori</h3>
                <?= Html::a('Semua Kategori', ['/site/categories']) ?>
                <?= Html::a('Games', ['/site/categories', 'q' => 'game']) ?>
                <?= Html::a('Streaming', ['/site/categories', 'q' => 'streaming']) ?>
                <?= Html::a('PPOB', ['/site/categories', 'q' => 'ppob']) ?>
            </div>
            <div class="col-6 col-lg-2">
                <h3 class="lp-footer-title">Bantuan</h3>
                <?= Html::a('Cara Topup', ['/site/cara-topup']) ?>
                <?= Html::a('Track Order', ['/site/track-order']) ?>
                <?= Html::a('Kontak', ['/site/contact']) ?>
            </div>
            <div class="col-lg-4">
                <h3 class="lp-footer-title">Metode Pembayaran</h3>
                <div class="payment-grid">
                    <?php foreach (['QRIS', 'BCA', 'BNI', 'Mandiri', 'DANA', 'OVO', 'GoPay', 'ShopeePay'] as $method): ?>
                        <span><?= Html::encode($method) ?></span>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
        <div class="lp-footer-bottom">
            <span>&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></span>
            <div class="lp-legal-links">
                <?= Html::a('Syarat & Ketentuan', ['/site/syarat-ketentuan']) ?>
                <?= Html::a('Kebijakan Pengembalian Dana (Refund Policy)', ['/site/kebijakan-pengembalian-dana']) ?>
                <?= Html::a('FAQ', ['/site/faq']) ?>
            </div>
        </div>
    </div>
</footer>
