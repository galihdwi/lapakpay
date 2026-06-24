<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class Provider extends ActiveRecord
{
    public static function collectionName()
    {
        return 'providers';
    }

    public function attributes()
    {
        return [
            '_id',
            'name',
            'type',
            'api_url',
            'api_key',
            'api_secret',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['name', 'type', 'api_url', 'api_key', 'api_secret', 'status'], 'string'],
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
