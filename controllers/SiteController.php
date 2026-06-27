<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\Banner;
use app\models\ContactForm;
use app\repositories\ProductRepository;
use app\repositories\TransactionRepository;
use app\services\ProductService;
use app\services\TransactionService;
use app\models\LoginForm;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\Security;
use yii\mail\MailerInterface;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SiteController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly MailerInterface $mailer,
        private readonly Security $security,
        private readonly ProductService $productService,
        private readonly TransactionService $transactionService,
        private readonly TransactionRepository $transactionRepository,
        private readonly ProductRepository $productRepository,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'create-order' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'transparent' => true,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $favoriteCategories = $this->productService->getFavoriteCategories(10);

        return $this->render('index', [
            'heroBanners' => Banner::find()
                ->where(['status' => 'active'])
                ->orderBy(['sort_order' => SORT_ASC, 'created_at' => SORT_DESC])
                ->all(),
            'favoriteCategories' => $favoriteCategories,
            'popularCategories' => $this->popularCategoriesThisWeek(),
        ]);
    }

    private function popularCategoriesThisWeek(int $limit = 4): array
    {
        $activeCategories = $this->productService->getFavoriteCategories(100);
        if ($activeCategories === []) {
            return [];
        }

        $categoryMap = [];
        foreach ($activeCategories as $category) {
            foreach ([(string) $category->name, (string) $category->slug] as $key) {
                $normalizedKey = $this->normalizeCategoryKey($key);
                if ($normalizedKey !== '') {
                    $categoryMap[$normalizedKey] = $category;
                }
            }
        }

        $counts = [];
        $since = date('Y-m-d H:i:s', strtotime('-7 days'));

        foreach ($this->transactionRepository->purchasedSince($since) as $transaction) {
            $product = $this->productRepository->findById((string) $transaction->product_id);
            if ($product === null) {
                continue;
            }

            $category = null;
            foreach ([(string) $product->brand, (string) $product->category] as $candidate) {
                $category = $categoryMap[$this->normalizeCategoryKey($candidate)] ?? null;
                if ($category !== null) {
                    break;
                }
            }

            if ($category === null) {
                continue;
            }

            $categoryId = (string) $category->_id;
            if (!isset($counts[$categoryId])) {
                $counts[$categoryId] = [
                    'category' => $category,
                    'transactions' => 0,
                ];
            }

            $counts[$categoryId]['transactions']++;
        }

        usort($counts, static fn (array $left, array $right): int => $right['transactions'] <=> $left['transactions']);

        return array_slice($counts, 0, $limit);
    }

    private function normalizeCategoryKey(string $value): string
    {
        $value = strtolower(trim($value));
        return preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
    }

    public function actionTrackOrder(?string $invoice = null): string
    {
        $invoice = strtoupper(trim((string) ($invoice ?: Yii::$app->request->get('invoice', ''))));
        $transaction = null;
        $product = null;

        if ($invoice !== '') {
            $transaction = $this->transactionRepository->findByInvoiceNumber($invoice);
            if ($transaction !== null && $transaction->product_id !== null && $transaction->product_id !== '') {
                $product = $this->productRepository->findById($transaction->product_id);
            }
        }

        return $this->render('track-order', [
            'invoice' => $invoice,
            'transaction' => $transaction,
            'product' => $product,
        ]);
    }

    public function actionCategories(?string $q = null): string
    {
        $query = trim((string) ($q ?: Yii::$app->request->get('q', '')));

        return $this->render('categories', [
            'query' => $query,
            'categories' => $this->productService->getActiveCategories($query),
        ]);
    }

    public function actionCaraTopup(): string
    {
        return $this->render('cara-topup');
    }

    /**
     * Displays products list with categories (brands) and grouped products
     *
     * @return string
     */
    public function actionProducts(?string $slug = null): string
    {
        $slug = $slug ?: Yii::$app->request->get('slug');
        $selectedBrand = Yii::$app->request->get('category');
        $category = null;

        if ($slug !== null) {
            $category = $this->productService->getActiveCategoryBySlug($slug);
            if ($category === null) {
                throw new NotFoundHttpException('Produk tidak ditemukan.');
            }

            $selectedBrand = $category->name;
        }

        if ($selectedBrand === null || $selectedBrand === '') {
            throw new NotFoundHttpException('Produk tidak ditemukan.');
        }

        $brandCandidates = [$selectedBrand];
        if ($category !== null) {
            $brandCandidates[] = (string) $category->slug;
        }

        $products = $this->productService->getProductsByBrandCandidates($brandCandidates);
        $groupedProducts = $this->productService->groupProductsByCategory($products);

        return $this->render('products', [
            'category' => $category,
            'selectedBrand' => $selectedBrand,
            'products' => $products,
            'groupedProducts' => $groupedProducts,
        ]);
    }

    public function actionCreateOrder(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $productId = trim((string) $request->post('product_id', ''));
        $target = trim((string) $request->post('target', ''));
        $zone = trim((string) $request->post('zone', ''));
        $paymentMethod = trim((string) $request->post('payment_method', 'iPaymu'));
        $email = trim((string) $request->post('email', ''));

        if ($paymentMethod === '') {
            $paymentMethod = 'iPaymu';
        }

        if ($productId === '' || $target === '' || $email === '') {
            Yii::$app->response->statusCode = 422;
            return [
                'status' => 'error',
                'message' => 'Produk, User ID, dan email wajib diisi.',
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Yii::$app->response->statusCode = 422;
            return [
                'status' => 'error',
                'message' => 'Format email tidak valid.',
            ];
        }

        $product = $this->productService->getProductById($productId);
        if ($product === null || $product->status !== 'active') {
            Yii::$app->response->statusCode = 404;
            return [
                'status' => 'error',
                'message' => 'Produk tidak ditemukan atau tidak aktif.',
            ];
        }

        try {
            $transaction = $this->transactionService->createTopupTransaction(
                $product,
                $target,
                $zone !== '' ? $zone : null,
                $paymentMethod,
                Yii::$app->user->isGuest ? null : (string) Yii::$app->user->id,
            );

            $invoice = $this->transactionService->createPayment($transaction, [
                'email' => $email,
            ]);

            if (($invoice['status'] ?? null) !== 'success' || empty($invoice['payment_url'])) {
                Yii::$app->response->statusCode = 502;
                return [
                    'status' => 'error',
                    'message' => $invoice['message'] ?? 'Gagal membuat payment link.',
                    'invoice_number' => $transaction->invoice_number,
                ];
            }

            return [
                'status' => 'success',
                'invoice_number' => $transaction->invoice_number,
                'payment_url' => $invoice['payment_url'],
            ];
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'status' => 'error',
                'message' => 'Gagal membuat pesanan. Silakan coba lagi.',
            ];
        }
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm($this->security);

        if ($model->load($this->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact(): Response|string
    {
        $model = new ContactForm();

        $contact = $model->load($this->request->post()) && $model->contact(
            $this->mailer,
            Yii::$app->params['adminEmail'],
            Yii::$app->params['senderEmail'],
            Yii::$app->params['senderName'],
        );

        if ($contact) {
            Yii::$app->session->setFlash(
                'success',
                'Terima kasih sudah menghubungi AksesPay. Kami akan membalas secepatnya.',
            );

            return $this->refresh();
        }

        return $this->render('contact', ['model' => $model]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout(): string
    {
        return $this->render('about');
    }
}
