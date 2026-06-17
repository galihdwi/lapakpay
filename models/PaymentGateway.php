<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class PaymentGateway extends ActiveRecord
{
    public static function collectionName()
    {
        return 'payment_gateways';
    }

    public function attributes()
    {
        return [
            '_id',
            'name',
            'code',
            'api_url',
            'api_key',
            'private_key',
            'merchant_code',
            'priority',
            'fee_flat',
            'fee_percent',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
            [['priority'], 'integer'],
            [['fee_flat', 'fee_percent'], 'number'],
            [['name', 'code', 'api_url', 'api_key', 'private_key', 'merchant_code', 'status'], 'string'],
            [['status'], 'default', 'value' => 'active'],
            [['priority'], 'default', 'value' => 10],
            [['fee_flat', 'fee_percent'], 'default', 'value' => 0],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function beforeValidate()
    {
        $now = date('Y-m-d H:i:s');
        $this->updated_at = $now;

        if ($this->created_at === null || $this->created_at === '') {
            $this->created_at = $now;
        }

        return parent::beforeValidate();
    }
}
