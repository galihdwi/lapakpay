<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Banner;
use app\models\Category;
use app\models\Commission;
use app\models\Payment;
use app\models\PaymentGateway;
use app\models\Product;
use app\models\Promo;
use app\models\Provider;
use app\models\Transaction;
use app\models\User;
use app\models\WebhookLog;
use app\services\ProductService;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class DbController extends Controller
{
    public bool $force = false;
    public bool $syncProducts = false;
    public string $provider = 'vip-payment';
    public string $adminUsername = '';
    public string $adminEmail = '';
    public string $adminPassword = '';

    public function __construct(
        $id,
        $module,
        private readonly ProductService $productService,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
            'force',
            'syncProducts',
            'provider',
            'adminUsername',
            'adminEmail',
            'adminPassword',
        ]);
    }

    public function optionAliases(): array
    {
        return array_merge(parent::optionAliases(), [
            'f' => 'force',
            'sync-products' => 'syncProducts',
            'admin-username' => 'adminUsername',
            'admin-email' => 'adminEmail',
            'admin-password' => 'adminPassword',
        ]);
    }

    public function actionFresh(): int
    {
        $db = (string) (Yii::$app->mongodb->defaultDatabaseName ?? 'configured MongoDB');

        if (!$this->force && !$this->confirm("Hapus semua data MongoDB database '{$db}'?")) {
            $this->stdout('Dibatalkan.' . PHP_EOL);
            return ExitCode::OK;
        }

        foreach ($this->collections() as $collection) {
            try {
                Yii::$app->mongodb->createCommand(['drop' => $collection])->execute();
                $this->stdout("Dropped {$collection}" . PHP_EOL);
            } catch (\Throwable $exception) {
                $message = $exception->getMessage();
                if (!str_contains($message, 'ns not found')) {
                    $this->stderr("Skip {$collection}: {$message}" . PHP_EOL);
                }
            }
        }

        Yii::$app->cache->flush();
        $this->stdout('Cache cleared.' . PHP_EOL);

        if ($this->adminUsername !== '' || $this->adminEmail !== '' || $this->adminPassword !== '') {
            $adminResult = $this->createAdmin();
            if ($adminResult !== ExitCode::OK) {
                return $adminResult;
            }
        }

        if ($this->syncProducts) {
            $result = $this->productService->syncProvider($this->provider);
            $this->stdout("Product sync selesai. Diterima {$result['received']}, aktif {$result['active']}, tersimpan {$result['synced']}." . PHP_EOL);
        }

        $this->stdout('Fresh database selesai.' . PHP_EOL);

        return ExitCode::OK;
    }

    private function createAdmin(): int
    {
        if ($this->adminUsername === '' || $this->adminEmail === '' || $this->adminPassword === '') {
            $this->stderr('adminUsername, adminEmail, dan adminPassword wajib diisi bersama.' . PHP_EOL);
            return ExitCode::DATAERR;
        }

        $user = new User();
        $user->setAttributes([
            'username' => $this->adminUsername,
            'email' => $this->adminEmail,
            'role' => 'admin',
            'status' => 'active',
        ], false);
        $user->setPassword($this->adminPassword);
        $user->generateAuthKey();

        if (!$user->save()) {
            foreach ($user->getFirstErrors() as $error) {
                $this->stderr($error . PHP_EOL);
            }

            return ExitCode::DATAERR;
        }

        $this->stdout("Admin user dibuat: {$this->adminUsername}" . PHP_EOL);

        return ExitCode::OK;
    }

    private function collections(): array
    {
        return [
            Banner::collectionName(),
            Category::collectionName(),
            Commission::collectionName(),
            Payment::collectionName(),
            PaymentGateway::collectionName(),
            Product::collectionName(),
            Promo::collectionName(),
            Provider::collectionName(),
            Transaction::collectionName(),
            User::collectionName(),
            WebhookLog::collectionName(),
        ];
    }
}
