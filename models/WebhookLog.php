<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class WebhookLog extends ActiveRecord
{
    public static function collectionName()
    {
        return 'webhooks';
    }

    public function attributes()
    {
        return [
            '_id',
            'provider',
            'event',
            'payload',
            'headers',
            'status',
            'notes',
            'created_at',
        ];
    }

    public function rules()
    {
        return [
            [['provider'], 'required'],
            [['payload', 'headers'], 'safe'],
            [['provider', 'event', 'status', 'notes'], 'string'],
            [['status'], 'default', 'value' => 'received'],
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
