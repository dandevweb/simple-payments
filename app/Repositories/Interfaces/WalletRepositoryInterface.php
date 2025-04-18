<?php

namespace App\Repositories\Interfaces;

use App\Models\Wallet;

interface WalletRepositoryInterface
{
    public function getBalance(Wallet $wallet): float;

    public function decrementBalance(Wallet $wallet, float $amount): void;

    public function incrementBalance(Wallet $wallet, float $amount): void;
}
