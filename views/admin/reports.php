<?php

/** @var yii\web\View $this */
/** @var array $stats */
/** @var app\models\Transaction[] $latestTransactions */

use yii\helpers\Html;

$this->title = 'Laporan';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<section class="admin-section">
    <div class="container-xl">
    <div class="admin-page-head">
        <div>
            <div class="section-kicker">Laporan</div>
            <h1 class="section-title">Laporan</h1>
            <p class="lp-muted mb-0">Ringkasan performa transaksi dan profit saat ini.</p>
        </div>
        <?= Html::a('Kembali', ['index'], ['class' => 'lp-btn lp-btn-ghost']) ?>
    </div>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <span>Total Transaksi</span><strong><?= Html::encode((string) $stats['transactions']) ?></strong>
        </div>
        <div class="admin-stat-card">
            <span>Omzet</span><strong>Rp <?= Html::encode(number_format((float) $stats['grossSales'], 0, ',', '.')) ?></strong>
        </div>
        <div class="admin-stat-card">
            <span>Profit</span><strong>Rp <?= Html::encode(number_format((float) $stats['profit'], 0, ',', '.')) ?></strong>
        </div>
        <div class="admin-stat-card">
            <span>Produk</span><strong><?= Html::encode((string) $stats['products']) ?></strong>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="admin-panel h-100">
                <h2 class="h5">Status Transaksi</h2>
                <?php if (empty($stats['byStatus'])): ?>
                    <p class="lp-muted mb-0">Belum ada transaksi.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush admin-list">
                        <?php foreach ($stats['byStatus'] as $status => $count): ?>
                            <li class="list-group-item d-flex justify-content-between bg-transparent px-0">
                                <span><?= Html::encode($status) ?></span>
                                <strong><?= Html::encode((string) $count) ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="admin-panel h-100">
                <h2 class="h5">Transaksi Terbaru</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle admin-table mb-0">
                        <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Status</th>
                            <th>Gateway</th>
                            <th>Nominal</th>
                            <th>Profit</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($latestTransactions as $transaction): ?>
                            <tr>
                                <td><?= Html::encode((string) $transaction->invoice_number) ?></td>
                                <td><?= Html::encode((string) $transaction->status) ?></td>
                                <td><?= Html::encode((string) $transaction->payment_gateway) ?></td>
                                <td>Rp <?= Html::encode(number_format((float) $transaction->sell_price, 0, ',', '.')) ?></td>
                                <td>Rp <?= Html::encode(number_format((float) $transaction->profit, 0, ',', '.')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($latestTransactions)): ?>
                            <tr>
                                <td colspan="5" class="lp-muted">Belum ada transaksi.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
