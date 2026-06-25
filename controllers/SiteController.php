<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\ContactForm;
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
        return $this->render('index', [
            'favoriteCategories' => $this->productService->getFavoriteCategories(),
        ]);
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
        $paymentMethod = trim((string) $request->post('payment_method', 'Flip'));
        $email = trim((string) $request->post('email', ''));

        if ($paymentMethod === '') {
            $paymentMethod = 'Flip';
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
                'name' => Yii::$app->user->isGuest ? 'Customer' : (string) Yii::$app->user->identity->username,
                'email' => $email,
            ]);

            if (($invoice['status'] ?? null) !== 'success' || empty($invoice['payment_url'])) {
                Yii::$app->response->statusCode = 502;
                return [
                    'status' => 'error',
                    'message' => $invoice['message'] ?? 'Gagal membuat payment link Flip.',
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
                'Thank you for contacting us. We will respond to you as soon as possible.',
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
