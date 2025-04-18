<?php

namespace App\Providers;

use App\Repositories\LogRepository;
use App\Repositories\Interfaces\LogRepositoryInterface;
use App\Repositories\Interfaces\TransferRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Repositories\TransferRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(TransferRepositoryInterface::class, TransferRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(LogRepositoryInterface::class, LogRepository::class);
    }
}
