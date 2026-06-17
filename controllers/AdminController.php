<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Category;
use app\models\Product;
use app\repositories\ProductRepository;
use app\models\User;
use app\models\Transaction;
use app\models\PaymentGateway;
use app\models\WebhookLog;
use app\models\Banner;
use app\models\Commission;
use app\models\Promo;
use app\repositories\AdminRepository;
use app\models\Provider;
use Yii;
use yii\helpers\FileHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\helpers\Inflector;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class AdminController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly AdminRepository $adminRepository,
        private readonly ProductRepository $productRepository,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => static fn() => Yii::$app->user->identity?->role === 'admin',
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'sections' => $this->sections(),
            'stats' => $this->dashboardStats(),
        ]);
    }

    public function actionManage(string $section): string
    {
        $config = $this->sectionConfig($section);
        $query = $this->adminRepository->query($config['class']);

        foreach (($config['fixedFilters'] ?? []) as $attribute => $value) {
            $query->andWhere([$attribute => $value]);
        }

        foreach (($config['searchAttributes'] ?? []) as $attribute) {
            $value = trim((string) Yii::$app->request->get($attribute, ''));
            if ($value !== '') {
                $query->andWhere([$attribute => $value]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => $config['defaultOrder'] ?? ['_id' => SORT_DESC],
            ],
        ]);

        return $this->render('manage', [
            'section' => $section,
            'config' => $config,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate(string $section): Response|string
    {
        $config = $this->sectionConfig($section);
        $model = new $config['class']($config['defaultValues'] ?? []);

        if ($this->loadAndSave($model, $config)) {
            Yii::$app->session->setFlash('success', $config['title'] . ' berhasil ditambahkan.');
            return $this->redirect(['manage', 'section' => $section]);
        }

        return $this->render('form', [
            'section' => $section,
            'config' => $config,
            'model' => $model,
        ]);
    }

    public function actionUpdate(string $section, string $id): Response|string
    {
        $config = $this->sectionConfig($section);
        $model = $this->findModel($config['class'], $id);

        if ($this->loadAndSave($model, $config)) {
            Yii::$app->session->setFlash('success', $config['title'] . ' berhasil diperbarui.');
            return $this->redirect(['manage', 'section' => $section]);
        }

        return $this->render('form', [
            'section' => $section,
            'config' => $config,
            'model' => $model,
        ]);
    }

    public function actionDelete(string $section, string $id): Response
    {
        $config = $this->sectionConfig($section);
        $this->adminRepository->delete($this->findModel($config['class'], $id));
        Yii::$app->session->setFlash('success', $config['title'] . ' berhasil dihapus.');

        return $this->redirect(['manage', 'section' => $section]);
    }

    public function actionReports(): string
    {
        return $this->render('reports', [
            'stats' => $this->dashboardStats(),
            'latestTransactions' => $this->adminRepository->query(Transaction::class)
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(10)
                ->all(),
        ]);
    }

    private function loadAndSave($model, array $config): bool
    {
        if (!$model->load(Yii::$app->request->post())) {
            $this->prepareJsonAttributes($model, $config);
            return false;
        }

        $this->applyPassword($model);
        if (!$this->parseJsonAttributes($model, $config)) {
            return false;
        }
        if (!$this->handleUploads($model, $config)) {
            return false;
        }

        $saved = $this->adminRepository->save($model);
        if (!$saved) {
            $this->prepareJsonAttributes($model, $config);
        }

        return $saved;
    }

    private function applyPassword($model): void
    {
        if (!$model instanceof User) {
            return;
        }

        $password = trim((string) $model->password);
        if ($password !== '') {
            $model->setPassword($password);
        }

        if ($model->auth_key === null || $model->auth_key === '') {
            $model->generateAuthKey();
        }

        if ($model->role === 'reseller' && ($model->api_key === null || $model->api_key === '')) {
            $model->api_key = Yii::$app->security->generateRandomString(40);
        }
    }

    private function prepareJsonAttributes($model, array $config): void
    {
        foreach (($config['jsonAttributes'] ?? []) as $attribute) {
            $value = $model->{$attribute};
            if (is_array($value)) {
                $model->{$attribute} = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
        }
    }

    private function parseJsonAttributes($model, array $config): bool
    {
        foreach (($config['jsonAttributes'] ?? []) as $attribute) {
            $value = trim((string) $model->{$attribute});
            if ($value === '') {
                $model->{$attribute} = [];
                continue;
            }

            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $model->addError($attribute, 'Format JSON tidak valid: ' . json_last_error_msg());
                return false;
            }

            $model->{$attribute} = $decoded;
        }

        return true;
    }

    private function handleUploads($model, array $config): bool
    {
        foreach (($config['uploadAttributes'] ?? []) as $attribute => $uploadConfig) {
            $file = UploadedFile::getInstance($model, $attribute);
            if ($file === null) {
                continue;
            }

            $targetAttribute = $uploadConfig['targetAttribute'] ?? $attribute;
            $directory = $uploadConfig['directory'] ?? 'uploads';
            $baseName = Inflector::slug((string) ($model->name ?? pathinfo($file->baseName, PATHINFO_FILENAME)));
            $fileName = $baseName . '-' . date('YmdHis') . '.' . strtolower($file->extension);
            $relativePath = trim($directory, '/') . '/' . $fileName;
            $absoluteDirectory = Yii::getAlias('@webroot/' . trim($directory, '/'));

            FileHelper::createDirectory($absoluteDirectory);

            if (!$file->saveAs($absoluteDirectory . DIRECTORY_SEPARATOR . $fileName)) {
                $model->addError($attribute, 'Gagal mengupload gambar.');
                return false;
            }

            $model->{$targetAttribute} = $relativePath;
        }

        return true;
    }

    private function findModel(string $class, string $id)
    {
        try {
            return $this->adminRepository->find($class, $id);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException('Data tidak ditemukan.');
        }
    }

    private function sectionConfig(string $section): array
    {
        $sections = $this->sections();
        if (!isset($sections[$section])) {
            throw new NotFoundHttpException('Menu admin tidak ditemukan.');
        }

        return $sections[$section];
    }

    private function getProductBrands(): array
    {
        $brandList = $this->productRepository->findAllBrands();

        if ($brandList === []) {
            return [];
        }

        return array_combine($brandList, $brandList);
    }

    private function sections(): array
    {
        $statusOptions = ['active' => 'Active', 'inactive' => 'Inactive', 'pending' => 'Pending', 'blocked' => 'Blocked'];
        $productBrands = $this->getProductBrands();

        return [
            'users' => [
                'title' => 'User',
                'icon' => 'people',
                'class' => User::class,
                'fixedFilters' => ['role' => 'user'],
                'defaultValues' => ['role' => 'user', 'status' => 'active', 'balance' => 0],
                'searchAttributes' => ['username', 'email', 'status'],
                'columns' => ['username', 'email', 'balance', 'status', 'created_at'],
                'fields' => [
                    ['username', 'text'],
                    ['email', 'text'],
                    ['password', 'password', 'hint' => 'Isi untuk membuat/mengganti password.'],
                    ['balance', 'number'],
                    ['status', 'dropDownList', 'items' => $statusOptions],
                ],
            ],
            'resellers' => [
                'title' => 'Reseller',
                'icon' => 'person-badge',
                'class' => User::class,
                'fixedFilters' => ['role' => 'reseller'],
                'defaultValues' => ['role' => 'reseller', 'status' => 'active', 'balance' => 0, 'margin' => 0],
                'searchAttributes' => ['username', 'email', 'status'],
                'columns' => ['username', 'email', 'balance', 'margin', 'status', 'created_at'],
                'fields' => [
                    ['username', 'text'],
                    ['email', 'text'],
                    ['password', 'password', 'hint' => 'Isi untuk membuat/mengganti password.'],
                    ['balance', 'number'],
                    ['margin', 'number'],
                    ['api_key', 'text'],
                    ['webhook_url', 'text'],
                    ['status', 'dropDownList', 'items' => $statusOptions],
                ],
            ],
            'transactions' => [
                'title' => 'Transaksi',
                'icon' => 'receipt',
                'class' => Transaction::class,
                'searchAttributes' => ['invoice_number', 'status', 'provider', 'payment_gateway'],
                'columns' => ['invoice_number', 'target', 'provider', 'payment_gateway', 'sell_price', 'profit', 'status', 'created_at'],
                'fields' => [
                    ['invoice_number', 'text'],
                    ['trxid_provider', 'text'],
                    ['user_id', 'text'],
                    ['product_id', 'text'],
                    ['target', 'text'],
                    ['zone', 'text'],
                    ['nickname', 'text'],
                    ['provider', 'text'],
                    ['payment_method', 'text'],
                    ['payment_gateway', 'text'],
                    ['buy_price', 'number'],
                    ['sell_price', 'number'],
                    ['profit', 'number'],
                    ['status', 'dropDownList', 'items' => ['pending' => 'Pending', 'paid' => 'Paid', 'processing' => 'Processing', 'success' => 'Success', 'failed' => 'Failed', 'expired' => 'Expired']],
                    ['notes', 'textarea'],
                ],
            ],
            'products' => [
                'title' => 'Produk',
                'icon' => 'box',
                'class' => Product::class,
                'searchAttributes' => ['provider', 'provider_code', 'category', 'brand', 'status'],
                'columns' => ['provider_code', 'product_name', 'category', 'brand', 'base_price', 'user_price', 'reseller_price', 'stock', 'status'],
                'jsonAttributes' => ['config'],
                'fields' => [
                    ['provider', 'text'],
                    ['provider_code', 'text'],
                    ['brand', 'text'],
                    ['product_name', 'text'],
                    ['description', 'textarea'],
                    ['base_price', 'number'],
                    ['user_price', 'number'],
                    ['reseller_price', 'number'],
                    ['stock', 'number'],
                    ['status', 'dropDownList', 'items' => ['active' => 'Active', 'inactive' => 'Inactive', 'empty' => 'Empty']],
                    ['config', 'textarea'],
                ],
            ],
            'suppliers' => [
                'title' => 'Supplier',
                'icon' => 'truck',
                'class' => Provider::class,
                'searchAttributes' => ['name', 'type', 'status'],
                'columns' => ['name', 'type', 'api_url', 'status', 'updated_at'],
                'fields' => [
                    ['name', 'text'],
                    ['type', 'dropDownList', 'items' => ['vip-reseller' => 'VIP Reseller', 'digiflazz' => 'Digiflazz', 'mitra' => 'Mitra', 'custom' => 'Custom']],
                    ['api_url', 'text'],
                    ['api_key', 'text'],
                    ['api_secret', 'text'],
                    ['status', 'dropDownList', 'items' => $statusOptions],
                ],
            ],
            'payment-gateways' => [
                'title' => 'Payment Gateway',
                'icon' => 'credit-card',
                'class' => PaymentGateway::class,
                'searchAttributes' => ['name', 'code', 'status'],
                'columns' => ['name', 'code', 'priority', 'fee_flat', 'fee_percent', 'status', 'updated_at'],
                'fields' => [
                    ['name', 'text'],
                    ['code', 'dropDownList', 'items' => ['mayar' => 'Mayar']],
                    ['api_url', 'text'],
                    ['api_key', 'text'],
                    ['private_key', 'text'],
                    ['merchant_code', 'text'],
                    ['priority', 'number'],
                    ['fee_flat', 'number'],
                    ['fee_percent', 'number'],
                    ['status', 'dropDownList', 'items' => $statusOptions],
                ],
            ],
            'banners' => [
                'title' => 'Banner',
                'icon' => 'image',
                'class' => Banner::class,
                'searchAttributes' => ['title', 'status'],
                'columns' => ['title', 'image', 'link', 'sort_order', 'status'],
                'fields' => [
                    ['title', 'text'],
                    ['image', 'text'],
                    ['link', 'text'],
                    ['sort_order', 'number'],
                    ['status', 'dropDownList', 'items' => $statusOptions],
                ],
            ],
            'categories' => [
                'title' => 'Kategori',
                'icon' => 'tags',
                'class' => Category::class,
                'searchAttributes' => ['name', 'slug', 'status'],
                'columns' => [
                    'name',
                    [
                        'label' => 'Jumlah Produk',
                        'value' => fn (Category $model): int => $this->adminRepository
                            ->query(Product::class)
                            ->where(['brand' => $model->name])
                            ->count(),
                    ],
                    'slug',
                    'image',
                    'status',
                ],
                'fields' => [
                    [
                        'name',
                        'dropDownList',
                        'items' => $productBrands,
                        'hint' => 'Nama kategori diambil dari brand produk. Brand yang sama otomatis dikelompokkan menjadi satu kategori.',
                    ],
                    [
                        'imageFile',
                        'file',
                        'hint' => 'Upload gambar kategori. File disimpan ke web/uploads/categories.',
                    ],
                    ['status', 'dropDownList', 'items' => $statusOptions],
                ],
                'uploadAttributes' => [
                    'imageFile' => [
                        'targetAttribute' => 'image',
                        'directory' => 'uploads/categories',
                    ],
                ],
            ],
            'promos' => [
                'title' => 'Promo',
                'icon' => 'percent',
                'class' => Promo::class,
                'searchAttributes' => ['code', 'type', 'status'],
                'columns' => ['code', 'name', 'type', 'value', 'quota', 'start_date', 'end_date', 'status'],
                'fields' => [
                    ['code', 'text'],
                    ['name', 'text'],
                    ['type', 'dropDownList', 'items' => ['fixed' => 'Nominal', 'percent' => 'Persen']],
                    ['value', 'number'],
                    ['quota', 'number'],
                    ['minimum_transaction', 'number'],
                    ['start_date', 'text', 'hint' => 'Format: YYYY-MM-DD HH:MM:SS'],
                    ['end_date', 'text', 'hint' => 'Format: YYYY-MM-DD HH:MM:SS'],
                    ['status', 'dropDownList', 'items' => $statusOptions],
                ],
            ],
            'commissions' => [
                'title' => 'Komisi',
                'icon' => 'cash-coin',
                'class' => Commission::class,
                'searchAttributes' => ['role', 'category', 'brand', 'status'],
                'columns' => ['name', 'role', 'category', 'brand', 'product_code', 'type', 'value', 'status'],
                'fields' => [
                    ['name', 'text'],
                    ['role', 'dropDownList', 'items' => ['user' => 'User', 'reseller' => 'Reseller']],
                    ['category', 'text'],
                    ['brand', 'text'],
                    ['product_code', 'text'],
                    ['type', 'dropDownList', 'items' => ['fixed' => 'Nominal', 'percent' => 'Persen']],
                    ['value', 'number'],
                    ['status', 'dropDownList', 'items' => $statusOptions],
                ],
            ],
            'webhooks' => [
                'title' => 'Webhook',
                'icon' => 'activity',
                'class' => WebhookLog::class,
                'searchAttributes' => ['provider', 'event', 'status'],
                'columns' => ['provider', 'event', 'status', 'notes', 'created_at'],
                'jsonAttributes' => ['payload'],
                'fields' => [
                    ['provider', 'text'],
                    ['event', 'text'],
                    ['payload', 'textarea'],
                    ['status', 'dropDownList', 'items' => ['received' => 'Received', 'processed' => 'Processed', 'failed' => 'Failed']],
                    ['notes', 'textarea'],
                ],
            ],
        ];
    }

    private function dashboardStats(): array
    {
        $transactions = $this->adminRepository->query(Transaction::class)->all();
        $grossSales = 0;
        $profit = 0;
        $byStatus = [];

        foreach ($transactions as $transaction) {
            $grossSales += (float) $transaction->sell_price;
            $profit += (float) $transaction->profit;
            $status = (string) ($transaction->status ?: 'unknown');
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
        }

        return [
            'users' => $this->adminRepository->query(User::class)->where(['role' => 'user'])->count(),
            'resellers' => $this->adminRepository->query(User::class)->where(['role' => 'reseller'])->count(),
            'products' => $this->adminRepository->query(Product::class)->count(),
            'transactions' => count($transactions),
            'grossSales' => $grossSales,
            'profit' => $profit,
            'byStatus' => $byStatus,
        ];
    }

    public static function labelFromAttribute(string $attribute): string
    {
        return Inflector::camel2words(str_replace('_', ' ', $attribute));
    }
}
