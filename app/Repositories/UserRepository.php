<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function findUserWithBalance(int $payerId): User
    {
        return User::query()
            ->with('wallet:id,balance,user_id')
            ->findOrFail($payerId);
    }
}
