<?php

/** @var yii\web\View $this */
/** @var app\models\Category|null $category */
/** @var string $selectedBrand */
/** @var app\models\Product[] $products */
/** @var array $groupedProducts */
/** @var array $productNicknameConfigs */
/** @var array|null $nicknameConfig */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Topup ' . $selectedBrand . ' - AksesPay';
$this->params['breadcrumbs'][] = ['label' => 'Home', 'url' => ['/site/index']];
$this->params['breadcrumbs'][] = $selectedBrand;

$resolveImage = static function (?string $image) use ($selectedBrand): string {
    $image = trim((string) $image);
    if ($image === '') {
        return 'https://placehold.co/1200x600/1E293B/FFFFFF?text=' . rawurlencode($selectedBrand);
    }

    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
        return $image;
    }

    return Url::to('@web/' . ltrim($image, '/'));
};

$coverImage = $resolveImage($category?->image ?? null);
$lowestPrice = null;
$initialProduct = null;
$initialPrice = 0.0;
foreach ($products as $product) {
    $price = !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'reseller'
        ? (float) $product->reseller_price
        : (float) $product->user_price;
    $lowestPrice = $lowestPrice === null ? $price : min($lowestPrice, $price);

    if ($initialProduct === null) {
        $initialProduct = $product;
        $initialPrice = $price;
    }
}
$initialNicknameConfig = $initialProduct !== null
    ? ($productNicknameConfigs[(string) $initialProduct->_id] ?? $nicknameConfig)
    : $nicknameConfig;

?>

<section class="game-detail-hero" style="--game-cover: url('<?= Html::encode($coverImage) ?>')">
    <div class="container-xl">
        <div class="game-detail-hero-inner">
            <div class="game-cover-card">
                <img src="<?= Html::encode($coverImage) ?>" alt="<?= Html::encode($selectedBrand) ?>" loading="lazy">
            </div>
            <div class="game-hero-copy">
                <div class="section-kicker">Produk Digital</div>
                <h1><?= Html::encode($selectedBrand) ?></h1>
                <p>Topup cepat, pembayaran aman, dan produk diproses otomatis dari supplier aktif AksesPay.</p>
                <div class="game-trust-row">
                    <span>Proses cepat</span>
                    <span>Layanan 24/7</span>
                    <span>Pembayaran aman</span>
                </div>
                <?php if ($lowestPrice !== null): ?>
                    <div class="game-price-pill">Mulai Rp<?= Html::encode(number_format($lowestPrice, 0, ',', '.')) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="content-section">
    <div class="container-xl">
        <div class="game-detail-layout">
            <aside class="game-info-panel game-detail-info">
                <div class="game-info-body">
                    <span class="hero-pill">Instant Delivery</span>
                    <h2>Informasi <?= Html::encode($selectedBrand) ?></h2>
                    <p>Masukkan data akun dengan benar, pilih nominal produk, lalu selesaikan pembayaran. Pesanan akan masuk ke antrean otomatis setelah pembayaran berhasil.</p>
                    <ul>
                        <li>Masukkan User ID dan Zone ID jika produk membutuhkan zone.</li>
                        <li>Pilih nominal sesuai kebutuhan.</li>
                        <li>Bayar dengan QRIS, e-wallet, atau virtual account.</li>
                        <li>Simpan invoice untuk pelacakan transaksi.</li>
                    </ul>
                </div>
            </aside>

            <div
                class="game-checkout-flow"
                data-checkout
                data-create-order-url="<?= Html::encode(Url::to(['/site/create-order'])) ?>"
                data-check-nickname-url="<?= Html::encode(Url::to(['/site/check-game-nickname'])) ?>"
                data-nickname-required="<?= $initialNicknameConfig !== null ? '1' : '0' ?>"
                data-requires-zone="<?= ($initialNicknameConfig['requiresZone'] ?? false) ? '1' : '0' ?>"
            >
                <div class="checkout-step product-detail-step">
                    <div class="step-label">1</div>
                    <h2>Masukkan Data Akun</h2>
                    <p class="lp-muted">Pastikan data akun sesuai dengan akun tujuan.</p>
                    <div class="row g-2">
                        <div class="<?= ($initialNicknameConfig['requiresZone'] ?? true) ? 'col-md-7' : 'col-12' ?>" data-target-field>
                            <input class="form-control lp-input" placeholder="User ID" data-order-target>
                        </div>
                        <div class="col-md-5 <?= ($initialNicknameConfig !== null && !($initialNicknameConfig['requiresZone'] ?? false)) ? 'd-none' : '' ?>" data-zone-field>
                            <input class="form-control lp-input" placeholder="<?= Html::encode((string) ($initialNicknameConfig['zonePlaceholder'] ?? 'Zone ID / Server')) ?>" data-order-zone>
                        </div>
                    </div>
                    <div class="nickname-result d-none" data-nickname-result></div>
                </div>

                <div class="checkout-step product-detail-step">
                    <div class="step-label">2</div>
                    <h2>Pilih Produk</h2>
                    <?php if (empty($groupedProducts)): ?>
                        <div class="nickname-result">Belum ada produk aktif untuk <?= Html::encode($selectedBrand) ?>.</div>
                    <?php else: ?>
                        <?php $firstProductRendered = false; ?>
                        <?php foreach ($groupedProducts as $groupName => $items): ?>
                            <div class="product-choice-group">
                                <h3><?= Html::encode((string) $groupName) ?></h3>
                                <div class="product-choice-grid">
                                    <?php foreach ($items as $index => $product): ?>
                                        <?php
                                        $price = !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'reseller'
                                            ? (float) $product->reseller_price
                                            : (float) $product->user_price;
                                        $productNicknameConfig = $productNicknameConfigs[(string) $product->_id] ?? null;
                                        ?>
                                        <button
                                            type="button"
                                            class="select-card product-choice-card <?= !$firstProductRendered ? 'is-active' : '' ?>"
                                            data-select-card="nominal"
                                            data-product-id="<?= Html::encode((string) $product->_id) ?>"
                                            data-product-name="<?= Html::encode((string) $product->product_name) ?>"
                                            data-product-price="<?= Html::encode((string) (int) round($price)) ?>"
                                            data-nickname-required="<?= $productNicknameConfig !== null ? '1' : '0' ?>"
                                            data-requires-zone="<?= ($productNicknameConfig['requiresZone'] ?? false) ? '1' : '0' ?>"
                                            data-zone-placeholder="<?= Html::encode((string) ($productNicknameConfig['zonePlaceholder'] ?? 'Zone ID / Server')) ?>"
                                        >
                                            <span><?= Html::encode($product->product_name) ?></span>
                                            <strong>Rp<?= Html::encode(number_format($price, 0, ',', '.')) ?></strong>
                                        </button>
                                        <?php $firstProductRendered = true; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="checkout-step product-detail-step">
                    <div class="step-label">3</div>
                    <h2>Data Pembeli</h2>
                    <input class="form-control lp-input" placeholder="Email untuk invoice dan bukti pembayaran" data-order-email>
                    <div class="nickname-result d-none mt-3" data-order-message></div>
                </div>
            </div>

            <aside class="order-summary game-order-summary">
                <h2>Detail Pembayaran</h2>
                <div><span>Produk</span><strong><?= Html::encode($selectedBrand) ?></strong></div>
                <div><span>Item</span><strong data-summary-item><?= Html::encode($initialProduct?->product_name ?? 'Pilih produk') ?></strong></div>
                <div class="total"><span>Total Bayar</span><strong data-summary-total>Rp<?= Html::encode(number_format($initialPrice, 0, ',', '.')) ?></strong></div>
                <button type="button" class="lp-btn lp-btn-primary w-100" data-buy-now>Beli Sekarang</button>
                <p class="lp-muted small mb-0 mt-3">Pesanan akan diproses setelah pembayaran terkonfirmasi.</p>
            </aside>
        </div>
    </div>
</section>
