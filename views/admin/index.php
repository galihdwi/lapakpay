<?php

/** @var yii\web\View $this */
/** @var array $sections */
/** @var array $stats */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Admin';
$this->params['breadcrumbs'][] = $this->title;

$cards = [
    ['label' => 'User', 'value' => $stats['users']],
    ['label' => 'Reseller', 'value' => $stats['resellers']],
    ['label' => 'Produk', 'value' => $stats['products']],
    ['label' => 'Transaksi', 'value' => $stats['transactions']],
    ['label' => 'Omzet', 'value' => 'Rp ' . number_format((float) $stats['grossSales'], 0, ',', '.')],
    ['label' => 'Profit', 'value' => 'Rp ' . number_format((float) $stats['profit'], 0, ',', '.')],
];
?>

<section class="admin-section">
    <div class="container-xl">
    <div class="admin-page-head">
        <div>
            <div class="section-kicker">Admin Panel</div>
            <h1 class="section-title">Admin LapakPay</h1>
            <p class="lp-muted mb-0">Kelola operasional topup, PPOB, reseller, supplier, dan payment gateway.</p>
        </div>
        <?= Html::a('Laporan', ['reports'], ['class' => 'lp-btn lp-btn-primary']) ?>
    </div>

    <div class="admin-stats-grid">
        <?php foreach ($cards as $card): ?>
            <div class="admin-stat-card">
                <span><?= Html::encode($card['label']) ?></span>
                <strong><?= Html::encode((string) $card['value']) ?></strong>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="admin-menu-grid">
        <?php foreach ($sections as $section => $config): ?>
            <a class="admin-menu-card" href="<?= Url::to(['manage', 'section' => $section]) ?>">
                <span><?= Html::encode(strtoupper(substr($config['title'], 0, 2))) ?></span>
                <strong><?= Html::encode($config['title']) ?></strong>
                <small>Tambah, edit, cari, dan hapus data <?= Html::encode(strtolower($config['title'])) ?>.</small>
            </a>
        <?php endforeach; ?>
    </div>
    </div>
</section>
