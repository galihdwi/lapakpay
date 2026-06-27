<?php

namespace app\commands;

use yii\console\Controller;
use app\services\ProductService;

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
            $result = $this->productService->syncProvider($providerName);

            if ($result['received'] === 0) {
                echo "No product data received from provider.\n";
                return 1;
            }

            echo "Received {$result['received']} products, {$result['active']} active.\n";
            echo "Successfully synced {$result['synced']} active products.\n";
            if (($result['zeroPrice'] ?? 0) > 0) {
                echo "Warning: {$result['zeroPrice']} active products have zero price from provider mapping.\n";
            }

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

    public function actionVipPayment()
    {
        return $this->actionProvider('vip-payment');
    }
}
