<?php

namespace app\repositories;

use yii\base\InvalidArgumentException;
use yii\mongodb\ActiveRecord;

class AdminRepository
{
    public function query(string $modelClass)
    {
        $this->assertActiveRecord($modelClass);

        return $modelClass::find();
    }

    public function find(string $modelClass, string $id): ActiveRecord
    {
        $this->assertActiveRecord($modelClass);
        $model = $modelClass::findOne($id);

        if ($model === null) {
            throw new InvalidArgumentException('Data tidak ditemukan.');
        }

        return $model;
    }

    public function save(ActiveRecord $model): bool
    {
        return $model->save();
    }

    public function delete(ActiveRecord $model): bool
    {
        return (bool) $model->delete();
    }

    private function assertActiveRecord(string $modelClass): void
    {
        if (!is_subclass_of($modelClass, ActiveRecord::class)) {
            throw new InvalidArgumentException("{$modelClass} must be a MongoDB ActiveRecord.");
        }
    }
}
