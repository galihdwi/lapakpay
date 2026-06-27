<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login Admin';
$this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Login admin AksesPay untuk mengelola produk, transaksi, dan konfigurasi layanan.';
$this->params['meta_keywords'] = 'aksespay, login admin, topup game';
$htmlIcon = <<<HTML
{label}<div class="input-group login-input-group"><span class="input-group-text" aria-hidden="true">%s</span>{input}</div>{error}{hint}
HTML;
$labelOptions = ['class' => 'form-label'];
?>
<div class="site-login">
    <div class="login-split-card">
        <div class="row g-0">
            <div class="col-lg-5 d-none d-lg-flex login-brand-panel">
                <div class="login-brand-content">
                    <div>
                        <div class="login-brand-mark">AP</div>
                    </div>
                    <div>
                        <div class="section-kicker text-white-50">AksesPay Console</div>
                        <h2 class="login-brand-title">Kelola transaksi digital dengan tenang.</h2>
                        <p class="login-brand-text">Masuk untuk mengatur produk, sinkronisasi supplier, payment gateway, dan monitoring order.</p>
                    </div>
                    <div class="login-brand-pills">
                        <span>Produk</span>
                        <span>Transaksi</span>
                        <span>Gateway</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="login-form-panel">
                    <div class="login-head">
                        <div class="login-mobile-brand d-lg-none">
                            <span>AP</span>
                            <strong>AksesPay</strong>
                        </div>
                        <h1><?= Html::encode($this->title) ?></h1>
                        <p class="lp-muted mb-0">Masukkan akun admin untuk melanjutkan.</p>
                    </div>

                    <?php $form = ActiveForm::begin(['id' => 'login-form', 'options' => ['class' => 'login-form']]); ?>

                        <?= $form->field($model, 'username', [
                            'options' => ['class' => 'login-field'],
                            'template' => sprintf($htmlIcon, '&#128100;'),
                            'inputOptions' => [
                                'class' => 'form-control lp-input',
                                'placeholder' => 'Username',
                                'autofocus' => true,
                                'autocomplete' => 'username',
                            ],
                        ])->textInput()->label('Username', $labelOptions) ?>

                        <?= $form->field($model, 'password', [
                            'options' => ['class' => 'login-field'],
                            'template' => sprintf($htmlIcon, '&#128274;'),
                            'inputOptions' => [
                                'class' => 'form-control lp-input',
                                'placeholder' => 'Password',
                                'autocomplete' => 'current-password',
                            ],
                        ])->passwordInput()->label('Password', $labelOptions) ?>

                    <div class="login-remember">
                        <?= $form->field($model, 'rememberMe')->checkbox() ?>
                    </div>

                    <?= Html::submitButton('Masuk', [
                        'class' => 'lp-btn lp-btn-primary login-btn w-100',
                        'name' => 'login-button',
                    ]) ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
