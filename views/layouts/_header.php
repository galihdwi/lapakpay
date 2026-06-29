<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use app\components\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;

$items = [
    [
        'label' => 'Home',
        'url' => ['/site/index'],
    ],
    [
        'label' => 'Kategori',
        'url' => ['/site/categories'],
    ],
    [
        'label' => 'Track Order',
        'url' => ['/site/track-order'],
    ],
    [
        'label' => 'Cara Topup',
        'url' => ['/site/cara-topup'],
    ],
    [
        'label' => 'Kontak',
        'url' => ['/site/contact'],
    ],
    [
        'label' => 'Admin',
        'url' => ['/admin/index'],
        'visible' => !Yii::$app->user->isGuest && Yii::$app->user->identity?->role === 'admin',
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
            'brandLabel' => Html::img(Url::to('@web/images/aksespay-logo.png'), [
                'class' => 'navbar-logo',
                'alt' => 'AksesPay',
            ]),
            'brandUrl' => Yii::$app->homeUrl,
            'encodeBrand' => false,
            'options' => ['class' => 'navbar-expand-lg navbar-dark fixed-top lp-navbar']
        ],
    ); ?>
    <form class="lp-search d-none d-lg-flex" role="search">
        <span aria-hidden="true"><i class="bi bi-search"></i></span>
        <input type="search" placeholder="Cari Mobile Legends, Netflix, PLN..." aria-label="Search produk">
    </form>
    <?= Nav::widget(
        [
            'options' => ['class' => 'navbar-nav ms-auto align-items-lg-center lp-nav'],
            'encodeLabels' => false,
            'items' => $items,
        ],
    ) ?>
    <?= Html::button(
        '<i class="bi bi-circle-half" aria-hidden="true"></i>',
        [
            'id' => 'theme-toggle',
            'class' => 'btn btn-sm lp-icon-btn ms-lg-2',
            'aria-label' => 'Switch to dark mode',
        ],
    ) ?>
    <?php NavBar::end() ?>
</header>
