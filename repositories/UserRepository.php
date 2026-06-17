<?php

namespace app\repositories;

use app\models\User;

class UserRepository
{
    public function findByUsername(string $username): ?User
    {
        return User::findOne(['username' => $username]);
    }

    public function save(User $user): bool
    {
        return $user->save();
    }
}
