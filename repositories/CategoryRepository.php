<?php

namespace app\repositories;

use app\models\Category;

class CategoryRepository
{
    public function findActiveBySlug(string $slug): ?Category
    {
        return Category::findOne(['slug' => $slug, 'status' => 'active']);
    }

    public function findFavoriteActive(int $limit = 10): array
    {
        return Category::find()
            ->where(['status' => 'active'])
            ->orderBy(['name' => SORT_ASC])
            ->limit($limit)
            ->all();
    }

    public function save(Category $category): bool
    {
        return $category->save();
    }
}
