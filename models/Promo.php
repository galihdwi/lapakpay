<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class Promo extends ActiveRecord
{
    public static function collectionName()
    {
        return 'promos';
    }

    public function attributes()
    {
        return [
            '_id',
            'code',
            'name',
            'type',
            'value',
            'quota',
            'minimum_transaction',
            'start_date',
            'end_date',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public function rules()
    {
        return [
            [['code', 'type', 'value'], 'required'],
            [['value', 'minimum_transaction'], 'number'],
            [['quota'], 'integer'],
            [['code'], 'unique'],
            [['code', 'name', 'type', 'status'], 'string'],
            [['quota', 'minimum_transaction'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 'active'],
            [['start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
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
