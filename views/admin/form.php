<?php

/** @var yii\web\View $this */
/** @var string $section */
/** @var array $config */
/** @var yii\mongodb\ActiveRecord $model */

use app\controllers\AdminController;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$isNew = $model->isNewRecord;
$hasFileInput = !empty(array_filter($config['fields'], static fn (array $field): bool => ($field[1] ?? null) === 'file'));
$this->title = ($isNew ? 'Tambah ' : 'Edit ') . $config['title'];
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Kelola ' . $config['title'], 'url' => ['manage', 'section' => $section]];
$this->params['breadcrumbs'][] = $this->title;
?>

<section class="admin-section">
    <div class="container-xl">
    <div class="admin-page-head">
        <div>
            <div class="section-kicker"><?= $isNew ? 'Tambah Data' : 'Edit Data' ?></div>
            <h1 class="section-title"><?= Html::encode($this->title) ?></h1>
            <p class="lp-muted mb-0">Pastikan credential production diisi hanya pada environment yang aman.</p>
        </div>
        <?= Html::a('Kembali', ['manage', 'section' => $section], ['class' => 'lp-btn lp-btn-ghost']) ?>
    </div>

    <div class="admin-panel admin-form-panel">
        <?php $form = ActiveForm::begin(['options' => array_filter([
            'class' => 'row g-3',
            'enctype' => $hasFileInput ? 'multipart/form-data' : null,
        ])]); ?>
        <?php foreach ($config['fields'] as $field): ?>
            <?php
            [$attribute, $type] = $field;
            $options = $field['options'] ?? ['class' => 'form-control lp-input'];
            $items = $field['items'] ?? [];
            ?>
            <div class="<?= $type === 'textarea' ? 'col-12' : 'col-md-6 col-xl-4' ?>">
                <?php if ($type === 'dropDownList'): ?>
                    <?= $form->field($model, $attribute)->dropDownList($items, ['class' => 'form-select lp-input', 'prompt' => 'Pilih']) ?>
                <?php elseif ($type === 'textarea'): ?>
                    <?= $form->field($model, $attribute)->textarea(['rows' => 5, 'class' => 'form-control lp-input font-monospace']) ?>
                <?php elseif ($type === 'file'): ?>
                    <?= $form->field($model, $attribute)->fileInput(['class' => 'form-control lp-input']) ?>
                <?php else: ?>
                    <?= $form->field($model, $attribute)->input($type, $options) ?>
                <?php endif; ?>

                <?php if (!empty($field['hint'])): ?>
                    <div class="form-text"><?= Html::encode($field['hint']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="col-12 admin-actions">
            <?= Html::submitButton('Simpan', ['class' => 'lp-btn lp-btn-primary']) ?>
            <?= Html::a('Batal', ['manage', 'section' => $section], ['class' => 'lp-btn lp-btn-ghost']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    </div>
</section>
