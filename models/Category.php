<?php

namespace app\models;

use yii\helpers\Inflector;
use yii\mongodb\ActiveRecord;

/**
 * Category model for MongoDB.
 *
 * @property \MongoDB\BSON\ObjectId $_id
 * @property string $name
 * @property string $slug
 * @property string $image
 * @property string $status
 */
class Category extends ActiveRecord
{
    public $imageFile;

    public static function collectionName()
    {
        return 'categories';
    }

    public function attributes()
    {
        return [
            '_id',
            'name',
            'slug',
            'image',
            'status',
        ];
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['slug'], 'default', 'value' => static fn (self $model): string => Inflector::slug($model->name)],
            [['slug'], 'unique'],
            [['name'], 'unique'],
            [['name', 'slug', 'image', 'status'], 'string'],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif']],
            [['status'], 'default', 'value' => 'active'],
        ];
    }

    public function beforeValidate()
    {
        if ($this->slug === null || $this->slug === '') {
            $this->slug = Inflector::slug((string) $this->name);
        }

        return parent::beforeValidate();
    }
}
