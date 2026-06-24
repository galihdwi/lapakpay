<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class Banner extends ActiveRecord
{
    public static function collectionName()
    {
        return 'banners';
    }

    public function attributes()
    {
        return [
            '_id',
            'title',
            'image',
            'link',
            'sort_order',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public function rules()
    {
        return [
            [['title', 'image'], 'required'],
            [['title', 'image', 'link', 'status'], 'string'],
            [['sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
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
