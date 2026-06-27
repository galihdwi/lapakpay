<?php

/** @var yii\web\View $this */
/** @var app\models\Category[] $categories */
/** @var string $query */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Kategori Produk';
$this->params['meta_description'] = 'Jelajahi semua kategori produk aktif AksesPay dan cari game, streaming, PPOB, atau voucher favorit.';
$this->params['meta_keywords'] = 'kategori produk, topup game, ppob, streaming, aksespay';

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
?>

<section class="categories-page">
    <div class="container-xl">
        <div class="categories-hero">
            <div>
                <div class="section-kicker">Kategori Produk</div>
                <h1>Temukan produk yang kamu butuhkan</h1>
                <p>Cari kategori aktif dari admin, lalu pilih untuk melihat daftar produk dan nominal yang tersedia.</p>
            </div>
            <div class="categories-count">
                <strong><?= Html::encode((string) count($categories)) ?></strong>
                <span><?= $query !== '' ? 'hasil ditemukan' : 'kategori aktif' ?></span>
            </div>
        </div>

        <div class="categories-search-panel">
            <?= Html::beginForm(['/site/categories'], 'get', ['class' => 'categories-search-form']) ?>
                <label class="visually-hidden" for="category-search">Cari kategori</label>
                <?= Html::textInput('q', $query, [
                    'id' => 'category-search',
                    'class' => 'form-control lp-input',
                    'placeholder' => 'Cari Mobile Legends, Free Fire, PLN, Netflix...',
                    'autocomplete' => 'off',
                ]) ?>
                <?= Html::submitButton('Cari', ['class' => 'lp-btn lp-btn-primary']) ?>
                <?php if ($query !== ''): ?>
                    <?= Html::a('Reset', ['/site/categories'], ['class' => 'lp-btn lp-btn-ghost']) ?>
                <?php endif; ?>
            <?= Html::endForm() ?>
        </div>

        <?php if ($categories !== []): ?>
            <div class="category-grid categories-grid-all">
                <?php foreach ($categories as $category): ?>
                    <?php
                    $name = (string) $category->name;
                    $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 2));
                    ?>
                    <a
                        href="<?= Url::to(['/site/products', 'slug' => (string) $category->slug]) ?>"
                        class="category-card category-card-image"
                        style="--category-image: url('<?= Html::encode($resolveCategoryImage((string) $category->image)) ?>')"
                    >
                        <span class="category-icon"><?= Html::encode($initials ?: 'AP') ?></span>
                        <strong><?= Html::encode($name) ?></strong>
                        <small>Lihat produk tersedia</small>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <?= $query !== ''
                    ? 'Kategori dengan kata kunci "' . Html::encode($query) . '" tidak ditemukan.'
                    : 'Belum ada kategori aktif.' ?>
            </div>
        <?php endif; ?>
    </div>
</section>
