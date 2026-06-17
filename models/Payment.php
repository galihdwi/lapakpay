<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

/**
 * Payment model for MongoDB.
 *
 * @property \MongoDB\BSON\ObjectId $_id
 * @property string $invoice_number
 * @property float $amount
 * @property string $gateway
 * @property string $gateway_reference
 * @property string $status
 * @property string $paid_at
 */
class Payment extends ActiveRecord
{
    public static function collectionName()
    {
        return 'payments';
    }

    public function attributes()
    {
        return [
            '_id',
            'invoice_number',
            'amount',
            'gateway',
            'gateway_reference',
            'status',
            'paid_at',
        ];
    }

    public function rules()
    {
        return [
            [['invoice_number', 'amount', 'gateway'], 'required'],
            [['amount'], 'number'],
            [['invoice_number', 'gateway', 'gateway_reference', 'status'], 'string'],
            [['status'], 'default', 'value' => 'pending'],
            [['paid_at'], 'safe'],
        ];
    }
}
