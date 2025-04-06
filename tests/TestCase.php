<?php

namespace Tests;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function createUserWithBalance(UserTypeEnum $type, float $balance): User
    {
        /** @var User $user */
        $user = User::factory()->create(['type' => $type]);
        $user->wallet()->create(['balance' => $balance]);

        return $user;
    }
}
