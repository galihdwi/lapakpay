<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

/**
 * Product model for MongoDB.
 *
 * @property \MongoDB\BSON\ObjectId $_id
 * @property string $provider
 * @property string $provider_code
 * @property string $category
 * @property string $brand
 * @property string $product_name
 * @property string $description
 * @property string $image
 * @property float $base_price
 * @property float $reseller_price
 * @property float $user_price
 * @property int $stock
 * @property string $status
 * @property array $config
 * @property string $updated_at
 */
class Product extends ActiveRecord
{
    public static function collectionName()
    {
        return 'products';
    }

    public function attributes()
    {
        return [
            '_id',
            'provider',
            'provider_code',
            'category',
            'brand',
            'product_name',
            'description',
            'image',
            'base_price',
            'reseller_price',
            'user_price',
            'stock',
            'status',
            'config',
            'updated_at',
        ];
    }

    public function rules()
    {
        return [
            [['provider', 'provider_code', 'product_name'], 'required'],
            [['base_price', 'reseller_price', 'user_price'], 'number'],
            [['stock'], 'integer'],
            [['config'], 'safe'],
            [['provider', 'provider_code', 'category', 'brand', 'product_name', 'description', 'image', 'status'], 'string'],
            [['stock'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 'active'],
            [['updated_at'], 'safe'],
        ];
    }

    public function beforeValidate()
    {
        $this->updated_at = date('Y-m-d H:i:s');

        if ($this->category === null || $this->category === '') {
            $this->category = $this->brand ?: 'product';
        }

        return parent::beforeValidate();
    }
}
