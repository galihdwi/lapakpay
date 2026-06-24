<?php

/** @var yii\web\View $this */
/** @var app\models\Category[] $favoriteCategories */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'LapakPay - Topup Game, PPOB, Streaming Premium';
$this->params['meta_description'] = 'Topup game, PPOB, dan streaming premium cepat dengan harga kompetitif dan pembayaran lengkap.';
$this->params['meta_keywords'] = 'topup game, mobile legends, free fire, ppob, netflix, spotify, lapakpay';

$heroSlides = [
    ['title' => 'Promo Diamond ML', 'copy' => 'Diskon sampai 35% untuk weekly pass dan diamond populer.', 'tag' => 'Mulai Rp1.000', 'image' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1400&q=80'],
    ['title' => 'Free Fire Spesial', 'copy' => 'Topup instan untuk push rank malam ini.', 'tag' => 'Bonus Voucher', 'image' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1400&q=80'],
    ['title' => 'Spotify & Netflix', 'copy' => 'Akun premium legal, aktif cepat, harga reseller tersedia.', 'tag' => 'Streaming Premium', 'image' => 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1400&q=80'],
];

$fallbackCategories = [
    ['name' => 'Mobile Legends', 'slug' => 'mobile-legends', 'image' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80'],
    ['name' => 'Free Fire', 'slug' => 'free-fire', 'image' => 'https://images.unsplash.com/photo-1600861194942-f883de0dfe96?auto=format&fit=crop&w=600&q=80'],
    ['name' => 'PUBG Mobile', 'slug' => 'pubg-mobile', 'image' => 'https://images.unsplash.com/photo-1560253023-3ec5d502959f?auto=format&fit=crop&w=600&q=80'],
    ['name' => 'Netflix', 'slug' => 'netflix', 'image' => 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?auto=format&fit=crop&w=600&q=80'],
];

$categoryCards = !empty($favoriteCategories)
    ? array_map(static fn ($category): array => [
        'name' => (string) $category->name,
        'slug' => (string) $category->slug,
        'image' => (string) $category->image,
    ], $favoriteCategories)
    : $fallbackCategories;

$resolveCategoryImage = static function (?string $image): string {
    $image = trim((string) $image);
    if ($image === '') {
        return 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=600&q=80';
    }

    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
        return $image;
    }

    return Url::to('@web/' . ltrim($image, '/'));
};

$popularProducts = [
    ['Mobile Legends', 'Mulai Rp1.250', 'Promo', 'Terlaris', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80'],
    ['Free Fire', 'Mulai Rp1.000', 'Diskon', 'Hot', 'https://images.unsplash.com/photo-1600861194942-f883de0dfe96?auto=format&fit=crop&w=600&q=80'],
    ['PUBG Mobile', 'Mulai Rp5.000', 'Bonus', 'Terlaris', 'https://images.unsplash.com/photo-1560253023-3ec5d502959f?auto=format&fit=crop&w=600&q=80'],
    ['Netflix Premium', 'Mulai Rp18.000', 'Legal', 'Baru', 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?auto=format&fit=crop&w=600&q=80'],
];

$flashSales = [
    ['86 Diamonds', 'Rp19.900', 'Rp22.500', 82],
    ['Weekly Pass', 'Rp26.500', 'Rp30.000', 68],
    ['Spotify 1 Bulan', 'Rp16.000', 'Rp20.000', 74],
];

$nominals = ['86 Diamonds', '172 Diamonds', '257 Diamonds', '344 Diamonds', '878 Diamonds', 'Twilight Pass', 'Weekly Pass'];
$payments = ['QRIS', 'DANA', 'OVO', 'BCA VA', 'Mandiri VA', 'Alfamart', 'Saldo Akun'];
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
                                    <span class="hero-pill"><?= Html::encode($slide['tag']) ?></span>
                                    <h2><?= Html::encode($slide['title']) ?></h2>
                                    <p><?= Html::encode($slide['copy']) ?></p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="#topup-detail" class="lp-btn lp-btn-primary">Topup Sekarang</a>
                                        <a href="#popular" class="lp-btn lp-btn-ghost">Mulai dari Rp1.000</a>
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
            <div class="category-grid">
                <?php foreach ($categoryCards as $category): ?>
                    <?php
                    $name = (string) $category['name'];
                    $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 2));
                    ?>
                    <a
                        href="<?= Url::to(['/site/products', 'slug' => $category['slug']]) ?>"
                        class="category-card category-card-image"
                        style="--category-image: url('<?= Html::encode($resolveCategoryImage($category['image'])) ?>')"
                    >
                        <span class="category-icon"><?= Html::encode($initials ?: 'LP') ?></span>
                        <strong><?= Html::encode($name) ?></strong>
                        <small>Produk tersedia</small>
                    </a>
                <?php endforeach ?>
            </div>
        </div>
    </section>

    <section id="popular" class="content-section">
        <div class="container-xl">
            <div class="section-head">
                <div>
                    <div class="section-kicker">Produk Populer</div>
                    <h2 class="section-title">Paling sering dibeli minggu ini</h2>
                </div>
            </div>
            <div class="product-grid">
                <?php foreach ($popularProducts as $product): ?>
                    <article class="product-card">
                        <img src="<?= Html::encode($product[4]) ?>" alt="<?= Html::encode($product[0]) ?>" loading="lazy">
                        <div class="product-body">
                            <div class="badge-row">
                                <span><?= Html::encode($product[2]) ?></span>
                                <span><?= Html::encode($product[3]) ?></span>
                            </div>
                            <h3><?= Html::encode($product[0]) ?></h3>
                            <p><?= Html::encode($product[1]) ?></p>
                            <a href="#topup-detail" class="lp-btn lp-btn-small">Topup</a>
                        </div>
                    </article>
                <?php endforeach ?>
            </div>
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

    <section id="topup-detail" class="content-section">
        <div class="container-xl">
            <div class="section-head">
                <div>
                    <div class="section-kicker">High Fidelity UI</div>
                    <h2 class="section-title">Detail Topup Mobile Legends</h2>
                </div>
            </div>
            <div class="topup-layout">
                <aside class="game-info-panel">
                    <img src="https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=900&q=80" alt="Mobile Legends topup preview" loading="lazy">
                    <div class="game-info-body">
                        <span class="hero-pill">Instant Delivery</span>
                        <h3>Mobile Legends</h3>
                        <p>Masukkan User ID dan Zone ID, pilih nominal, lalu bayar. Pesanan diproses otomatis oleh supplier aktif.</p>
                        <ul>
                            <li>Estimasi proses 10-60 detik</li>
                            <li>Nickname checker real-time</li>
                            <li>Harga user dan reseller terpisah</li>
                        </ul>
                    </div>
                </aside>
                <div class="checkout-panel">
                    <div class="checkout-step">
                        <div class="step-label">1</div>
                        <h3>Masukkan ID Game</h3>
                        <div class="row g-2">
                            <div class="col-7"><input class="form-control lp-input" placeholder="User ID"></div>
                            <div class="col-5"><input class="form-control lp-input" placeholder="Zone ID"></div>
                        </div>
                        <button type="button" class="lp-btn lp-btn-ghost mt-3" data-check-nickname>Cek Nickname</button>
                        <div class="nickname-result" data-nickname-result>Nickname dan region tampil di sini.</div>
                    </div>
                    <div class="checkout-step">
                        <div class="step-label">2</div>
                        <h3>Pilih Nominal</h3>
                        <div class="nominal-grid">
                            <?php foreach ($nominals as $index => $nominal): ?>
                                <button type="button" class="select-card <?= $index === 1 ? 'is-active' : '' ?>" data-select-card="nominal">
                                    <?= Html::encode($nominal) ?>
                                    <small>Mulai Rp<?= number_format(19000 + ($index * 8500), 0, ',', '.') ?></small>
                                </button>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <div class="checkout-step">
                        <div class="step-label">3</div>
                        <h3>Metode Pembayaran</h3>
                        <div class="payment-select-grid">
                            <?php foreach ($payments as $index => $payment): ?>
                                <button type="button" class="select-card <?= $index === 0 ? 'is-active' : '' ?>" data-select-card="payment">
                                    <?= Html::encode($payment) ?>
                                </button>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <div class="checkout-step">
                        <div class="step-label">4</div>
                        <h3>Data Pembeli</h3>
                        <div class="row g-2">
                            <div class="col-md-6"><input class="form-control lp-input" placeholder="Email"></div>
                            <div class="col-md-6"><input class="form-control lp-input" placeholder="Nomor WhatsApp"></div>
                        </div>
                    </div>
                    <div class="order-summary">
                        <div><span>Produk</span><strong>172 Diamonds</strong></div>
                        <div><span>Harga</span><strong>Rp39.500</strong></div>
                        <div><span>Biaya Admin</span><strong>Rp1.000</strong></div>
                        <div class="total"><span>Total Bayar</span><strong>Rp40.500</strong></div>
                        <button class="lp-btn lp-btn-primary w-100">Bayar Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="container-xl">
            <div class="value-grid">
                <?php foreach ([['Proses Otomatis', 'Order dikirim otomatis via queue dan provider layer.'], ['Pembayaran Aman', 'Xendit primary dan Tripay fallback.'], ['Produk Lengkap', 'Game, streaming, voucher, PLN, pulsa, data.'], ['24 Jam Online', 'Checkout dan fulfillment aktif sepanjang hari.']] as $value): ?>
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
                <div><strong data-count="128430">0</strong><span>Total Transaksi</span></div>
                <div><strong data-count="38420">0</strong><span>Total User</span></div>
                <div><strong data-count="842">0</strong><span>Total Produk</span></div>
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
    <a href="#dashboard">Riwayat</a>
    <a href="#dashboard">Saldo</a>
    <a href="#dashboard">Akun</a>
</nav>
