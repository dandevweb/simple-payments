<?php

namespace App\Providers;

use App\Repositories\WalletRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            WalletRepository::class,
            fn () => new WalletRepository()
        );
    }

    public function boot(): void
    {
        //
    }
}
