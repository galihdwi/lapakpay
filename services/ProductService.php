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
        private readonly ProviderRegistry $providerRegistry,
    ) {
    }

    public function syncProvider(string $providerName): array
    {
        $provider = $this->providerRegistry->get($providerName);
        $services = $provider->getServices();

        if (($services['result'] ?? true) === false) {
            throw new \RuntimeException($services['message'] ?? 'Provider rejected request.');
        }

        $items = $services['data'] ?? $services['services'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        $activeItems = array_values(array_filter(
            $items,
            fn ($item): bool => is_array($item) && $this->isActiveProviderItem($item),
        ));

        return [
            'received' => count($items),
            'active' => count($activeItems),
            'synced' => $this->syncFromProvider($activeItems, $providerName),
            'zeroPrice' => count(array_filter($activeItems, fn ($item): bool => is_array($item) && $this->resolveProviderPrice($item) <= 0)),
        ];
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

            if (!$this->isActiveProviderItem($item)) {
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
            $product->base_price = $this->resolveProviderPrice($item);
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

    private function resolveProviderPrice(array $item): float
    {
        foreach ([
            'price',
            'harga',
            'base_price',
            'basic',
            'price_basic',
            'harga_basic',
            'modal',
            'cost',
            'buy_price',
        ] as $attribute) {
            if (!array_key_exists($attribute, $item)) {
                continue;
            }

            $price = $this->normalizePrice($item[$attribute]);
            if ($price > 0) {
                return $price;
            }
        }

        return 0.0;
    }

    private function normalizePrice(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_array($value)) {
            foreach ($value as $nestedValue) {
                $price = $this->normalizePrice($nestedValue);
                if ($price > 0) {
                    return $price;
                }
            }

            return 0.0;
        }

        if (is_object($value)) {
            return 0.0;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return 0.0;
        }

        $value = preg_replace('/[^0-9,.-]/', '', $value) ?? '';

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (substr_count($value, '.') > 1) {
            $value = str_replace('.', '', $value);
        } elseif (substr_count($value, ',') === 1 && !str_contains($value, '.')) {
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function isActiveProviderItem(array $item): bool
    {
        $status = $item['status']
            ?? $item['product_status']
            ?? $item['service_status']
            ?? $item['available']
            ?? $item['is_active']
            ?? $item['active']
            ?? null;

        if ($status === null || $status === '') {
            return true;
        }

        if (is_bool($status)) {
            return $status;
        }

        $normalizedStatus = strtolower(trim((string) $status));

        return in_array($normalizedStatus, ['1', 'true', 'yes', 'active', 'available', 'normal', 'open', 'ready'], true);
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
