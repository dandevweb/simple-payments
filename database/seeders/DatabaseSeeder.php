<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(5)
            ->create(['type' => UserTypeEnum::Common])
            ->each(function ($user) {
                Wallet::factory()->create([
                    'user_id' => $user->id,
                    'balance' => 1000,
                ]);
            });

        User::factory()
            ->count(5)
            ->create(['type' => UserTypeEnum::Merchant])
            ->each(function ($user) {
                Wallet::factory()->create([
                    'user_id' => $user->id,
                    'balance' => 0,
                ]);
            });
    }
}
