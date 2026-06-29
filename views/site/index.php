<?php

/** @var yii\web\View $this */
/** @var app\models\Banner[] $heroBanners */
/** @var app\models\Category[] $favoriteCategories */
/** @var array[] $popularCategories */
/** @var array{transactions:int,users:int,products:int} $stats */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'AksesPay - Topup Game, PPOB, Streaming Premium';
$this->params['meta_description'] = 'Topup game, PPOB, dan streaming premium cepat dengan harga kompetitif dan pembayaran lengkap.';
$this->params['meta_keywords'] = 'topup game, mobile legends, free fire, ppob, netflix, spotify, aksespay';

$resolveAssetUrl = static function (?string $path): string {
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
        return $path;
    }

    return Url::to('@web/' . ltrim($path, '/'));
};

$activeHeroBanners = array_values(array_filter($heroBanners, static fn($banner): bool => trim((string) $banner->image) !== ''));

$heroSlides = !empty($activeHeroBanners)
    ? array_map(static fn($banner): array => [
        'title' => (string) $banner->title,
        'copy' => (string) $banner->subtitle,
        'tag' => (string) $banner->tag,
        'image' => $resolveAssetUrl($banner->image),
        'link' => (string) $banner->link,
    ], $activeHeroBanners)
    : [
        [
            'title' => 'AksesPay',
            'copy' => 'Topup game, PPOB, dan streaming premium cepat dengan pembayaran lengkap.',
            'tag' => 'Promo Pilihan',
            'image' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1400&q=80',
            'link' => '#popular',
        ],
    ];

$categoryCards = array_map(static fn($category): array => [
    'name' => (string) $category->name,
    'slug' => (string) $category->slug,
    'image' => (string) $category->image,
], array_slice($favoriteCategories, 0, 10));

$resolveCategoryImage = static function (?string $image) use ($resolveAssetUrl): string {
    $resolved = $resolveAssetUrl($image);
    if ($resolved === '') {
        return 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=600&q=80';
    }

    return $resolved;
};

$flashSales = [
    ['86 Diamonds', 'Rp19.900', 'Rp22.500', 82],
    ['Weekly Pass', 'Rp26.500', 'Rp30.000', 68],
    ['Spotify 1 Bulan', 'Rp16.000', 'Rp20.000', 74],
];

?>
<div class="store-page">

    <section class="hero-section">
        <div class="container-xl">
            <div id="promoCarousel" class="carousel slide lp-hero" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($heroSlides as $index => $slide): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <div class="hero-slide" style="--hero-image: url('<?= Html::encode($slide['image']) ?>')">
                                <div class="hero-content">
                                    <?php if ($slide['tag'] !== ''): ?>
                                        <span class="hero-pill"><?= Html::encode($slide['tag']) ?></span>
                                    <?php endif; ?>
                                    <h2><?= Html::encode($slide['title']) ?></h2>
                                    <?php if ($slide['copy'] !== ''): ?>
                                        <p><?= Html::encode($slide['copy']) ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="<?= Html::encode($slide['link'] ?: '#popular') ?>" class="lp-btn lp-btn-primary">Topup Sekarang</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev" aria-label="Previous promo">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next" aria-label="Next promo">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </section>

    <section id="categories" class="content-section">
        <div class="container-xl">
            <div class="section-head">
                <div>
                    <div class="section-kicker">Kategori Produk</div>
                    <h2 class="section-title">Pilih produk favorit</h2>
                </div>
                <a href="#popular" class="text-link">Lihat semua</a>
            </div>
            <?php if ($categoryCards !== []): ?>
                <div class="category-grid">
                    <?php foreach ($categoryCards as $category): ?>
                        <?php
                        $name = (string) $category['name'];
                        $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 2));
                        ?>
                        <a
                            href="<?= Url::to(['/site/products', 'slug' => $category['slug']]) ?>"
                            class="category-card category-card-image"
                            style="--category-image: url('<?= Html::encode($resolveCategoryImage($category['image'])) ?>')">
                            <span class="category-icon"><?= Html::encode($initials ?: 'LP') ?></span>
                            <strong><?= Html::encode($name) ?></strong>
                            <small>Produk tersedia</small>
                        </a>
                    <?php endforeach ?>
                </div>
            <?php else: ?>
                <div class="empty-state">Belum ada kategori aktif.</div>
            <?php endif; ?>
        </div>
    </section>

    <section id="popular" class="content-section">
        <div class="container-xl">
            <div class="section-head">
                <div>
                    <div class="section-kicker">Kategori Populer</div>
                    <h2 class="section-title">Paling sering dibeli minggu ini</h2>
                </div>
            </div>
            <?php if ($popularCategories !== []): ?>
                <div class="product-grid">
                    <?php foreach ($popularCategories as $item): ?>
                        <?php
                        $category = $item['category'];
                        $transactionCount = (int) $item['transactions'];
                        ?>
                        <article class="product-card">
                            <img src="<?= Html::encode($resolveCategoryImage((string) $category->image)) ?>" alt="<?= Html::encode((string) $category->name) ?>" loading="lazy">
                            <div class="product-body">
                                <div class="badge-row">
                                    <span>Kategori</span>
                                    <span><?= Html::encode((string) $transactionCount) ?> transaksi</span>
                                </div>
                                <h3><?= Html::encode((string) $category->name) ?></h3>
                                <p>Paling banyak dibeli dalam 7 hari terakhir.</p>
                                <a href="<?= Url::to(['/site/products', 'slug' => (string) $category->slug]) ?>" class="lp-btn lp-btn-small">Topup</a>
                            </div>
                        </article>
                    <?php endforeach ?>
                </div>
            <?php else: ?>
                <div class="empty-state">Belum ada transaksi kategori minggu ini.</div>
            <?php endif; ?>
        </div>
    </section>

    <section class="content-section">
        <div class="container-xl">
            <div class="flash-panel">
                <div class="flash-head">
                    <div>
                        <div class="section-kicker">Flash Sale</div>
                        <h2 class="section-title mb-0">Harga turun terbatas</h2>
                    </div>
                    <div class="countdown" data-countdown>04:18:42</div>
                </div>
                <div class="flash-list">
                    <?php foreach ($flashSales as $sale): ?>
                        <article class="flash-card">
                            <div class="discount-badge">-12%</div>
                            <div>
                                <h3><?= Html::encode($sale[0]) ?></h3>
                                <p><strong><?= Html::encode($sale[1]) ?></strong> <del><?= Html::encode($sale[2]) ?></del></p>
                                <div class="sale-progress"><span style="width: <?= (int) $sale[3] ?>%"></span></div>
                            </div>
                        </article>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="container-xl">
            <div class="value-grid">
                <?php foreach ([['Proses Otomatis', 'Order dikirim otomatis via queue dan provider layer.'], ['Pembayaran Aman', 'Pembayaran aman dengan payment gateway fallback.'], ['Produk Lengkap', 'Game, streaming, voucher, PLN, pulsa, data.'], ['24 Jam Online', 'Checkout dan fulfillment aktif sepanjang hari.']] as $value): ?>
                    <article class="value-card">
                        <span></span>
                        <h3><?= Html::encode($value[0]) ?></h3>
                        <p><?= Html::encode($value[1]) ?></p>
                    </article>
                <?php endforeach ?>
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="container-xl">
            <div class="stats-grid">
                <div><strong data-count="<?= Html::encode((string) ($stats['transactions'] ?? 0)) ?>">0</strong><span>Total Transaksi</span></div>
                <div><strong data-count="<?= Html::encode((string) ($stats['users'] ?? 0)) ?>">0</strong><span>Total User</span></div>
                <div><strong data-count="<?= Html::encode((string) ($stats['products'] ?? 0)) ?>">0</strong><span>Total Produk</span></div>
                <div><strong data-count="99" data-suffix="%">0%</strong><span>Success Rate</span></div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="container-xl">
            <div class="section-head">
                <div>
                    <div class="section-kicker">Testimoni</div>
                    <h2 class="section-title">Dipakai gamer dan reseller aktif</h2>
                </div>
            </div>
            <div class="testimonial-row">
                <?php foreach ([['Raka', 'Topup ML masuk kurang dari semenit, checkout QRIS jelas banget.'], ['Nadia', 'Harga reseller enak buat jual lagi, webhook API-nya mudah dipantau.'], ['Dimas', 'Paket streaming dan pulsa bisa satu tempat. UI mobile-nya ringan.']] as $review): ?>
                    <article class="testimonial-card">
                        <div class="avatar"><?= Html::encode(substr($review[0], 0, 1)) ?></div>
                        <strong><?= Html::encode($review[0]) ?></strong>
                        <div class="stars">★★★★★</div>
                        <p><?= Html::encode($review[1]) ?></p>
                    </article>
                <?php endforeach ?>
            </div>
        </div>
    </section>
</div>

<nav class="mobile-bottom-nav" aria-label="Mobile navigation">
    <a href="#main">Home</a>
    <a href="#categories">Kategori</a>
    <?= Html::a('Track Order', ['/site/track-order']) ?>
    <a href="#dashboard">Akun</a>
</nav>
