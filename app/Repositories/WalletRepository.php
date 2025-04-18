<?php

namespace App\Repositories;

use App\Models\Wallet;
use App\Repositories\Interfaces\WalletRepositoryInterface;

class WalletRepository implements WalletRepositoryInterface
{
    public function getBalance(Wallet $wallet): float
    {
        return (float) $wallet->balance;
    }

    public function decrementBalance(Wallet $wallet, float $amount): void
    {
        $wallet->update(['balance' => $wallet->balance - $amount]);
    }

    public function incrementBalance(Wallet $wallet, float $amount): void
    {
        $wallet->update(['balance' => $wallet->balance + $amount]);
    }
}
