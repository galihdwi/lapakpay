<?php

namespace app\repositories;

use app\models\Category;
use MongoDB\BSON\Regex;

class CategoryRepository
{
    public function findActiveBySlug(string $slug): ?Category
    {
        return Category::find()
            ->where(['slug' => $slug])
            ->andWhere(['status' => new Regex('^active$', 'i')])
            ->one();
    }

    public function findFavoriteActive(int $limit = 10): array
    {
        return Category::find()
            ->where(['status' => new Regex('^active$', 'i')])
            ->orderBy(['name' => SORT_ASC])
            ->limit($limit)
            ->all();
    }

    public function save(Category $category): bool
    {
        return $category->save();
    }
}
