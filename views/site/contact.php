<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\ContactForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\captcha\Captcha;

$this->title = 'Kontak AksesPay';
$this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Hubungi tim AksesPay untuk bantuan transaksi, kerja sama, dan dukungan pelanggan.';
$this->params['meta_keywords'] = 'kontak aksespay, bantuan topup, support aksespay, track order';
$htmlIcon = <<<HTML
{label}<div class="input-group contact-input-group"><span class="input-group-text" aria-hidden="true"><i class="bi %s"></i></span>{input}</div>{error}{hint}
HTML;
$labelOptions = ['class' => 'form-label'];
?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <section class="site-contact-success">
        <div class="container-xl">
            <div class="contact-success-panel">
                <span class="contact-success-mark"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
                <h1>Pesan terkirim</h1>
                <p><?= Html::encode(Yii::$app->session->getFlash('success')) ?></p>
                <?= Html::a('Kirim pesan lain', ['contact'], ['class' => 'lp-btn lp-btn-primary']) ?>
            </div>
        </div>
    </section>
<?php else: ?>
    <section class="site-contact">
        <div class="container-xl">
            <div class="contact-layout">
                <aside class="contact-info-panel">
                    <div>
                        <div class="section-kicker text-white-50">Bantuan AksesPay</div>
                        <h1>Butuh bantuan transaksi?</h1>
                        <p>Kirim detail kendala, nomor invoice, atau kebutuhan kerja sama. Tim kami akan mengecek dan menghubungi kamu kembali.</p>
                    </div>

                    <div class="contact-info-list">
                        <div>
                            <span>Email</span>
                            <strong><?= Html::encode(Yii::$app->params['adminEmail'] ?? 'support@aksespay.id') ?></strong>
                        </div>
                        <div>
                            <span>Track Order</span>
                            <?= Html::a('Cek nomor invoice', ['/site/track-order']) ?>
                        </div>
                        <div>
                            <span>Jam Bantuan</span>
                            <strong>Setiap hari, 09.00 - 22.00 WIB</strong>
                        </div>
                    </div>
                </aside>

                <div class="contact-form-panel">
                    <div class="contact-head">
                        <span class="contact-brand-mark">AP</span>
                        <div>
                            <h2>Kirim pesan</h2>
                            <p class="lp-muted mb-0">Isi form berikut dengan jelas agar kami bisa bantu lebih cepat.</p>
                        </div>
                    </div>

                    <?php $form = ActiveForm::begin(['id' => 'contact-form', 'options' => ['class' => 'contact-form']]); ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <?= $form->field($model, 'name', [
                                    'template' => sprintf($htmlIcon, 'bi-person'),
                                    'inputOptions' => [
                                        'class' => 'form-control lp-input',
                                        'placeholder' => 'Nama lengkap',
                                        'autofocus' => true,
                                    ],
                                ])->label('Nama', $labelOptions) ?>
                            </div>

                            <div class="col-md-6">
                                <?= $form->field($model, 'email', [
                                    'template' => sprintf($htmlIcon, 'bi-envelope'),
                                    'inputOptions' => [
                                        'class' => 'form-control lp-input',
                                        'placeholder' => 'email@example.com',
                                        'autocomplete' => 'email',
                                    ],
                                ])->label('Email', $labelOptions) ?>
                            </div>
                        </div>

                        <?= $form->field($model, 'subject', [
                            'template' => sprintf($htmlIcon, 'bi-chat-left-text'),
                            'inputOptions' => [
                                'class' => 'form-control lp-input',
                                'placeholder' => 'Contoh: Pembayaran belum masuk',
                            ],
                        ])->label('Subjek', $labelOptions) ?>

                        <?= $form->field($model, 'body', [
                            'template' => '{label}{input}{error}{hint}',
                            'inputOptions' => [
                                'class' => 'form-control lp-input contact-message',
                                'placeholder' => 'Tulis pesan dan nomor invoice jika ada.',
                            ],
                        ])->textarea(['rows' => 6])->label('Pesan', $labelOptions) ?>

                        <div class="contact-captcha-row">
                            <?= $form->field($model, 'verifyCode', [
                                'enableLabel' => false,
                                'inputOptions' => ['class' => 'form-control lp-input', 'placeholder' => 'Kode verifikasi'],
                            ])->widget(Captcha::class, [
                                'template' => '<div class="contact-captcha">{image}{input}</div>',
                            ]) ?>

                            <?= Html::submitButton('Kirim Pesan', [
                                'class' => 'lp-btn lp-btn-primary contact-submit',
                                'name' => 'contact-button',
                            ]) ?>
                        </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
