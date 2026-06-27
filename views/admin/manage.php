<?php

/** @var yii\web\View $this */
/** @var string $section */
/** @var array $config */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\controllers\AdminController;
use yii\bootstrap5\LinkPager;
use yii\grid\ActionColumn;
use yii\grid\CheckboxColumn;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Kelola ' . $config['title'];
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$bulkDeleteEnabled = !empty($config['bulkDelete']);
?>

<section class="admin-section">
    <div class="container-xl">
        <div class="admin-page-head">
            <div>
                <div class="section-kicker">Kelola Data</div>
                <h1 class="section-title"><?= Html::encode($this->title) ?></h1>
                <p class="lp-muted mb-0">Data tersimpan di MongoDB collection <?= Html::encode($config['class']::collectionName()) ?>.</p>
            </div>
            <div class="admin-actions">
                <?= Html::a('Kembali', ['index'], ['class' => 'lp-btn lp-btn-ghost']) ?>
                <?= Html::a('Tambah', ['create', 'section' => $section], ['class' => 'lp-btn lp-btn-primary']) ?>
            </div>
        </div>

        <?php if (!empty($config['searchAttributes'])): ?>
            <div class="admin-panel admin-filter-panel">
                <?= Html::beginForm(['manage', 'section' => $section], 'get', ['class' => 'row g-3 align-items-end']) ?>
                    <?php foreach ($config['searchAttributes'] as $attribute): ?>
                        <div class="col-md-3">
                            <?= Html::label(AdminController::labelFromAttribute($attribute), $attribute, ['class' => 'form-label']) ?>
                            <?= Html::textInput($attribute, Yii::$app->request->get($attribute), ['class' => 'form-control lp-input', 'id' => $attribute]) ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="col-md-auto">
                        <?= Html::submitButton('Cari', ['class' => 'lp-btn lp-btn-primary']) ?>
                        <?= Html::a('Reset', ['manage', 'section' => $section], ['class' => 'lp-btn lp-btn-ghost']) ?>
                    </div>
                <?= Html::endForm() ?>
            </div>
        <?php endif; ?>

        <div class="admin-panel admin-table-panel">
            <?php if ($bulkDeleteEnabled): ?>
                <?= Html::beginForm(['bulk-delete', 'section' => $section], 'post', ['class' => 'admin-bulk-form']) ?>
                <div class="admin-bulk-toolbar">
                    <?= Html::submitButton('Hapus Terpilih', [
                        'class' => 'lp-btn lp-btn-ghost admin-bulk-delete',
                        'data-confirm' => 'Hapus semua produk yang dipilih?',
                    ]) ?>
                </div>
            <?php endif; ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle admin-table'],
                'summaryOptions' => ['class' => 'lp-muted small mb-4'],
                'pager' => [
                    'class' => LinkPager::class,
                    'options' => ['class' => 'pagination justify-content-center'],
                    'linkOptions' => ['class' => 'page-link'],
                    'pageCssClass' => 'page-item',
                    'activePageCssClass' => 'active',
                    'disabledPageCssClass' => 'disabled',
                    'prevPageCssClass' => 'page-item',
                    'nextPageCssClass' => 'page-item',
                    'prevPageLabel' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>',
                    'nextPageLabel' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>',
                ],
                'columns' => array_merge(
                    $bulkDeleteEnabled ? [
                        [
                            'class' => CheckboxColumn::class,
                            'checkboxOptions' => static fn ($model): array => [
                                'value' => (string) $model->_id,
                                'class' => 'admin-row-checkbox',
                            ],
                            'contentOptions' => ['class' => 'admin-checkbox-cell'],
                            'headerOptions' => ['class' => 'admin-checkbox-cell'],
                        ],
                    ] : [],
                    array_map(static function (string|array $attribute): array {
                        if (is_array($attribute)) {
                            return $attribute;
                        }

                        return [
                            'attribute' => $attribute,
                            'label' => AdminController::labelFromAttribute($attribute),
                            'format' => 'raw',
                            'value' => static function ($model) use ($attribute): string {
                                $value = $model->{$attribute};
                                if (is_array($value)) {
                                    $value = json_encode($value, JSON_UNESCAPED_SLASHES);
                                }

                                $value = (string)$value;
                                if (strlen($value) > 80) {
                                    $value = substr($value, 0, 77) . '...';
                                }

                                return Html::encode($value);
                            },
                        ];
                    }, $config['columns']),
                    [
                        [
                            'class' => ActionColumn::class,
                            'template' => '{update} {delete}',
                            'urlCreator' => static function ($action, $model) use ($section): array {
                                return [$action, 'section' => $section, 'id' => (string)$model->_id];
                            },
                            'buttons' => [
                                'update' => static fn ($url, $model, $key): string => Html::a('Edit', $url, ['class' => 'admin-row-action me-1']),
                                'delete' => static fn ($url, $model, $key): string => Html::a('Hapus', $url, [
                                    'class' => 'admin-row-action admin-row-action-danger',
                                    'data-method' => 'post',
                                    'data-confirm' => 'Hapus data ini?',
                                ]),
                            ],
                        ],
                    ],
                ),
            ]) ?>
            <?php if ($bulkDeleteEnabled): ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
        </div>
    </div>
</section>
