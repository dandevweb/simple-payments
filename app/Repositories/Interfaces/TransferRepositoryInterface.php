<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface TransferRepositoryInterface
{
    public function createTransfer(User $payer, User $payee, float $value): void;
}
