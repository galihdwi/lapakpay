<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

/**
 * Transaction model for MongoDB.
 *
 * @property \MongoDB\BSON\ObjectId $_id
 * @property string $invoice_number
 * @property string $trxid_provider
 * @property string $user_id
 * @property string $product_id
 * @property string $target
 * @property string $zone
 * @property string $nickname
 * @property string $provider
 * @property string $payment_method
 * @property string $payment_gateway
 * @property float $buy_price
 * @property float $sell_price
 * @property float $profit
 * @property string $status
 * @property string $notes
 * @property string $created_at
 */
class Transaction extends ActiveRecord
{
    public static function collectionName()
    {
        return 'transactions';
    }

    public function attributes()
    {
        return [
            '_id',
            'invoice_number',
            'trxid_provider',
            'user_id',
            'product_id',
            'target',
            'zone',
            'nickname',
            'provider',
            'payment_method',
            'payment_gateway',
            'buy_price',
            'sell_price',
            'profit',
            'status',
            'notes',
            'created_at',
        ];
    }

    public function rules()
    {
        return [
            [['invoice_number', 'product_id', 'target', 'payment_method'], 'required'],
            [['buy_price', 'sell_price', 'profit'], 'number'],
            [[
                'invoice_number',
                'trxid_provider',
                'user_id',
                'product_id',
                'target',
                'zone',
                'nickname',
                'provider',
                'payment_method',
                'payment_gateway',
                'status',
                'notes',
            ], 'string'],
            [['status'], 'default', 'value' => 'pending'],
            [['created_at'], 'safe'],
        ];
    }

    public function beforeValidate()
    {
        if ($this->created_at === null || $this->created_at === '') {
            $this->created_at = date('Y-m-d H:i:s');
        }

        return parent::beforeValidate();
    }
}
