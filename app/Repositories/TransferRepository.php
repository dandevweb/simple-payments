<?php

namespace App\Repositories;

use App\Models\Transfer;
use App\Models\User;
use App\Repositories\Interfaces\TransferRepositoryInterface;

class TransferRepository implements TransferRepositoryInterface
{
    public function createTransfer(User $payer, User $payee, float $value): void
    {
        $transfer = Transfer::query()
            ->make([
                'value' => $value,
            ]);

        $transfer->fromWallet()->associate($payer);
        $transfer->toWallet()->associate($payee);
        $transfer->save();
    }
}
