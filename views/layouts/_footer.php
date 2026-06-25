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
                <div class="d-flex flex-wrap gap-2">
                    <span class="lp-social">IG</span>
                    <span class="lp-social">TT</span>
                    <span class="lp-social">X</span>
                    <span class="lp-social">YT</span>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h3 class="lp-footer-title">Kategori</h3>
                <a href="#categories">Games</a>
                <a href="#categories">Streaming</a>
                <a href="#categories">PPOB</a>
                <a href="#categories">Voucher</a>
            </div>
            <div class="col-6 col-lg-2">
                <h3 class="lp-footer-title">Bantuan</h3>
                <a href="#topup-detail">Cara Topup</a>
                <a href="#dashboard">Riwayat</a>
                <a href="#dashboard">Deposit</a>
                <a href="#contact">Kontak</a>
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
            <span>Secure checkout • Instant delivery • Reseller ready</span>
        </div>
    </div>
</footer>
