<?php

namespace App\Observers;

use App\Exceptions\TransferException;
use App\Models\Transfer;
use App\Notifications\TransferReceivedNotification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Notification;

class TransferObserver
{
    /**
     * @throws ConnectionException
     * @throws TransferException
     */
    public function created(Transfer $transfer): void
    {
        Notification::send(
            $transfer->fromWallet->user,
            new TransferReceivedNotification($transfer->value, $transfer->toWallet->user)
        );
    }
}
