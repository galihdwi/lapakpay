<?php

namespace app\services;

use app\models\Product;
use app\repositories\CategoryRepository;
use app\repositories\ProductRepository;
use Yii;

class ProductService
{
    private const PRODUCT_LIST_TTL = 900;
    private const CATEGORY_LIST_TTL = 3600;

    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function getFavoriteCategories(int $limit = 10): array
    {
        $cacheKey = ['catalog.favoriteCategories', $limit];

        return Yii::$app->cache->getOrSet(
            $cacheKey,
            fn (): array => $this->categoryRepository->findFavoriteActive($limit),
            self::CATEGORY_LIST_TTL,
        );
    }

    public function getProductsByBrand(string $brand): array
    {
        return $this->productRepository->findByBrand($brand);
    }

    public function getProductById(string $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function getProductsByBrandCandidates(array $candidates): array
    {
        return $this->productRepository->findByBrandCandidates($candidates);
    }

    public function getActiveCategoryBySlug(string $slug)
    {
        return $this->categoryRepository->findActiveBySlug($slug);
    }

    public function groupProductsByCategory(array $products): array
    {
        $groupedProducts = [];
        foreach ($products as $product) {
            $groupName = $product->category ?: 'Produk';
            $groupedProducts[$groupName][] = $product;
        }

        return $groupedProducts;
    }

    public function calculateSellingPrice(float $buyPrice, string $category, string $brand, ?string $productCode = null): array
    {
        $margin = $this->getMargin($productCode, $brand, $category);

        return [
            'user' => $buyPrice + $margin['user'],
            'reseller' => $buyPrice + $margin['reseller'],
        ];
    }

    public function syncFromProvider(array $providerData, string $providerName): int
    {
        $synced = 0;

        foreach ($providerData as $item) {
            if (!is_array($item)) {
                continue;
            }

            $code = (string) ($item['code'] ?? $item['service'] ?? $item['service_code'] ?? $item['id'] ?? '');
            if ($code === '') {
                continue;
            }

            $product = $this->productRepository->findByProviderCode($providerName, $code) ?: new Product();
            $product->provider = $providerName;
            $product->provider_code = $code;
            $product->product_name = (string) ($item['name'] ?? $item['product_name'] ?? $item['layanan'] ?? $code);
            $product->base_price = (float) ($item['price'] ?? $item['harga'] ?? $item['base_price'] ?? 0);
            $product->category = (string) ($item['category'] ?? $item['kategori'] ?? $item['type'] ?? 'game');
            $product->brand = (string) ($item['brand'] ?? $item['game'] ?? $item['operator'] ?? $product->category);
            $product->description = (string) ($item['description'] ?? $item['note'] ?? $product->description);
            $product->stock = (int) ($item['stock'] ?? $item['stok'] ?? $product->stock ?? 0);
            $product->config = $item;

            $prices = $this->calculateSellingPrice(
                (float) $product->base_price,
                (string) $product->category,
                (string) $product->brand,
                (string) $product->provider_code,
            );
            $product->user_price = $prices['user'];
            $product->reseller_price = $prices['reseller'];
            $product->status = 'active';
            $product->updated_at = date('Y-m-d H:i:s');

            if ($this->productRepository->save($product)) {
                $synced++;
            }
        }

        return $synced;
    }

    private function getMargin(?string $productCode, string $brand, string $category): array
    {
        $margins = Yii::$app->params['margins'] ?? [
            'global' => ['user' => 2000, 'reseller' => 1000],
        ];

        return $margins['product'][$productCode]
            ?? $margins['brand'][$brand]
            ?? $margins['category'][$category]
            ?? $margins['global'];
    }
}
