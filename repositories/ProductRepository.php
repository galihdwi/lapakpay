<?php

namespace app\repositories;

use app\models\Product;
use MongoDB\BSON\Regex;
use yii\mongodb\Query;

class ProductRepository
{
    public function findById($id): ?Product
    {
        return Product::findOne($id);
    }

    public function findByProviderCode(string $provider, string $code): ?Product
    {
        return Product::findOne(['provider' => $provider, 'provider_code' => $code]);
    }

    public function getAllActive(): array
    {
        return Product::find()->where(['status' => 'active'])->orderBy(['product_name' => SORT_ASC])->all();
    }

    public function save(Product $product): bool
    {
        return $product->save();
    }

    public function findByCategory(string $categorySlug): array
    {
        return Product::find()
            ->where(['category' => $categorySlug, 'status' => 'active'])
            ->orderBy(['product_name' => SORT_ASC])
            ->all();
    }

    public function findByBrand(string $brand): array
    {
        $brand = trim($brand);
        if ($brand === '') {
            return [];
        }

        $products = $this->queryByBrandOrCategory($brand);

        if ($products !== []) {
            return $products;
        }

        $normalizedBrand = $this->normalizeLookupValue($brand);

        return array_values(array_filter(
            Product::find()
                ->where(['status' => 'active'])
                ->orderBy(['product_name' => SORT_ASC, 'user_price' => SORT_ASC])
                ->all(),
            fn (Product $product): bool => in_array(
                $normalizedBrand,
                [
                    $this->normalizeLookupValue((string) $product->brand),
                    $this->normalizeLookupValue((string) $product->category),
                ],
                true,
            ),
        ));
    }

    public function findByBrandCandidates(array $candidates): array
    {
        $normalizedCandidates = array_values(array_unique(array_filter(
            array_map(fn ($candidate): string => $this->normalizeLookupValue((string) $candidate), $candidates),
            static fn (string $candidate): bool => $candidate !== '',
        )));

        if ($normalizedCandidates === []) {
            return [];
        }

        $directProducts = [];
        foreach ($candidates as $candidate) {
            $directProducts = array_merge($directProducts, $this->queryByBrandOrCategory((string) $candidate));
        }

        if ($directProducts !== []) {
            return $this->uniqueProducts($directProducts);
        }

        return $this->uniqueProducts(array_values(array_filter(
            Product::find()
                ->where(['status' => 'active'])
                ->orderBy(['product_name' => SORT_ASC, 'user_price' => SORT_ASC])
                ->all(),
            fn (Product $product): bool => $this->matchesAnyCandidate($product, $normalizedCandidates),
        )));
    }

    private function queryByBrandOrCategory(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        $escapedValue = preg_quote($value, '/');

        return Product::find()
            ->where(['status' => 'active'])
            ->andWhere([
                'or',
                ['brand' => $value],
                ['category' => $value],
                ['brand' => new Regex('^' . $escapedValue . '$', 'i')],
                ['category' => new Regex('^' . $escapedValue . '$', 'i')],
            ])
            ->orderBy(['product_name' => SORT_ASC, 'user_price' => SORT_ASC])
            ->all();
    }

    private function matchesAnyCandidate(Product $product, array $normalizedCandidates): bool
    {
        $productValues = [
            $this->normalizeLookupValue((string) $product->brand),
            $this->normalizeLookupValue((string) $product->category),
        ];

        foreach ($productValues as $productValue) {
            if ($productValue !== '' && in_array($productValue, $normalizedCandidates, true)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeLookupValue(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '', $value) ?? '';

        return $value;
    }

    /**
     * @param Product[] $products
     * @return Product[]
     */
    private function uniqueProducts(array $products): array
    {
        $unique = [];
        foreach ($products as $product) {
            $key = (string) ($product->_id ?? $product->provider_code ?? spl_object_id($product));
            $unique[$key] = $product;
        }

        return array_values($unique);
    }

    public function findAllBrands(): array
    {
        $brands = (new Query())
            ->select(['brand'])
            ->from(Product::collectionName())
            ->where(['status' => 'active'])
            ->distinct('brand');

        $brandList = array_values(array_unique(array_filter(
            array_map(static fn ($brand): string => trim((string) $brand), $brands),
            static fn (string $brand): bool => $brand !== '',
        )));
        sort($brandList);

        return $brandList;
    }
}
