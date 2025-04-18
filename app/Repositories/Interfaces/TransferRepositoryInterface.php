<?php

namespace App\Repositories\Interfaces;

use App\Models\Transfer;
use App\Models\User;

interface TransferRepositoryInterface
{
    public function create(User $payer, User $payee, float $value): Transfer;
}
