<?php

namespace App\Repositories;

use App\Models\Transfer;
use App\Models\User;
use App\Repositories\Interfaces\TransferRepositoryInterface;

class TransferRepository implements TransferRepositoryInterface
{
    public function create(User $payer, User $payee, float $value): Transfer
    {
        $transfer = Transfer::query()
            ->make([
                'value' => $value,
            ]);

        $transfer->fromWallet()->associate($payer);
        $transfer->toWallet()->associate($payee);
        $transfer->save();

        return $transfer;
    }
}
