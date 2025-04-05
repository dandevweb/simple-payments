<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(10)
            ->create()
            ->each(function ($user) {
                Wallet::factory()->create(['user_id' => $user->id]);
            });

        User::factory()
            ->count(5)
            ->state(['type' => UserTypeEnum::Merchant])
            ->create()
            ->each(function ($user) {
                Wallet::factory()->create(['user_id' => $user->id]);
            });
    }
}
