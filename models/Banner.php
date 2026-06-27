<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class Banner extends ActiveRecord
{
    public $imageFile;

    public static function collectionName()
    {
        return 'banners';
    }

    public function attributes()
    {
        return [
            '_id',
            'title',
            'subtitle',
            'tag',
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
            [['title'], 'required'],
            [['image'], 'required', 'when' => static fn (self $model): bool => $model->imageFile === null],
            [['title', 'subtitle', 'tag', 'image', 'link', 'status'], 'string'],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif']],
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
