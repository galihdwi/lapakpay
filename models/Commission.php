<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class Commission extends ActiveRecord
{
    public static function collectionName()
    {
        return 'commissions';
    }

    public function attributes()
    {
        return [
            '_id',
            'name',
            'role',
            'category',
            'brand',
            'product_code',
            'type',
            'value',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public function rules()
    {
        return [
            [['name', 'role', 'type', 'value'], 'required'],
            [['value'], 'number'],
            [['name', 'role', 'category', 'brand', 'product_code', 'type', 'status'], 'string'],
            [['status'], 'default', 'value' => 'active'],
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
