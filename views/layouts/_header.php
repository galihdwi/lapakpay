<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use app\components\NavBar;
use yii\helpers\Html;

$items = [
    [
        'label' => 'Home',
        'url' => ['/site/index'],
    ],
    [
        'label' => 'Kategori',
        'url' => ['/site/index', '#' => 'categories'],
    ],
    [
        'label' => 'Riwayat',
        'url' => ['/site/index', '#' => 'dashboard'],
    ],
    [
        'label' => 'Saldo',
        'url' => ['/site/index', '#' => 'dashboard'],
    ],
    [
        'label' => 'Admin',
        'url' => ['/admin/index'],
        'visible' => !Yii::$app->user->isGuest && Yii::$app->user->identity?->role === 'admin',
    ],
    [
        'label' => 'Login',
        'url' => ['/site/login'],
        'visible' => Yii::$app->user->isGuest,
    ],
    [
        'label' => 'Logout (' . Html::encode(Yii::$app->user->identity?->username ?? '') . ')',
        'url' => ['/site/logout'],
        'linkOptions' => [
            'data-method' => 'post',
            'class' => 'nav-link logout',
        ],
        'visible' => !Yii::$app->user->isGuest,
    ],
];

?>
<header id="header" class="lp-header">
    <?php NavBar::begin(
        [
            'brandLabel' => 'AksesPay',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar-expand-lg navbar-dark fixed-top lp-navbar']
        ],
    ); ?>
    <form class="lp-search d-none d-lg-flex" role="search">
        <span aria-hidden="true">⌕</span>
        <input type="search" placeholder="Cari Mobile Legends, Netflix, PLN..." aria-label="Search produk">
    </form>
    <?= Nav::widget(
        [
            'options' => ['class' => 'navbar-nav ms-auto align-items-lg-center lp-nav'],
            'encodeLabels' => false,
            'items' => $items,
        ],
    ) ?>
    <?= Html::a('Daftar', ['/site/login'], ['class' => 'btn btn-sm lp-btn lp-btn-primary ms-lg-2 d-none d-lg-inline-flex']) ?>
    <?= Html::button(
        '◐',
        [
            'id' => 'theme-toggle',
            'class' => 'btn btn-sm lp-icon-btn ms-lg-2',
            'aria-label' => 'Switch to dark mode',
        ],
    ) ?>
    <?php NavBar::end() ?>
</header>