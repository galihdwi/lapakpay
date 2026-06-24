<?php

namespace app\commands;

use yii\console\Controller;
use app\services\ProductService;
use Yii;

/**
 * SyncProductController handles product synchronization from suppliers.
 */
class SyncProductController extends Controller
{
    private $productService;

    public function __construct($id, $module, ProductService $productService, $config = [])
    {
        $this->productService = $productService;
        parent::__construct($id, $module, $config);
    }

    public function actionProvider($providerName = 'vip-reseller')
    {
        echo "Starting sync from {$providerName}...\n";
        
        try {
            $provider = Yii::$app->get('providerRegistry')->get($providerName);
            $services = $provider->getServices();

            if (($services['result'] ?? true) === false) {
                echo "Provider rejected request: " . ($services['message'] ?? 'Unknown error') . "\n";
                return 1;
            }

            $items = $services['data'] ?? $services['services'] ?? [];

            if (!is_array($items) || $items === []) {
                echo "No product data received from provider.\n";
                return 1;
            }

            $synced = $this->productService->syncFromProvider($items, $providerName);
            echo "Successfully synced {$synced} products.\n";

            return 0;
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return 1;
        }
    }

    /**
     * Backward-compatible alias.
     * Run: php yii sync-product/vip
     */
    public function actionVip()
    {
        return $this->actionProvider('vip-reseller');
    }
}
