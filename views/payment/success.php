<?php

use yii\helpers\Html;

/** @var string|null $invoiceNumber */

$this->title = 'Pembayaran Berhasil';
?>
<section class="py-5">
    <div class="container">
        <h1 class="h3 mb-3">Pembayaran Berhasil</h1>
        <p class="mb-4">
            Transaksi<?= $invoiceNumber ? ' ' . Html::encode($invoiceNumber) : '' ?> sedang diproses.
        </p>
        <?= Html::a('Kembali ke Beranda', ['/site/index'], ['class' => 'btn btn-primary']) ?>
    </div>
</section>
