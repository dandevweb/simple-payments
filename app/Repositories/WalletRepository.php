<?php

namespace App\Repositories;

use App\Models\Wallet;

class WalletRepository
{
    public function getBalance(Wallet $wallet): float
    {
        return (float) $wallet->balance;
    }

    public function decrementBalance(Wallet $wallet, float $amount): void
    {
        $wallet->decrement('balance', $amount);
    }

    public function incrementBalance(Wallet $wallet, float $amount): void
    {
        $wallet->increment('balance', $amount);
    }
}
