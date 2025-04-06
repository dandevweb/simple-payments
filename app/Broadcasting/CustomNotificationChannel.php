<?php

namespace App\Broadcasting;

use App\Exceptions\TransferException;
use App\Notifications\TransferReceivedNotification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CustomNotificationChannel
{
    /**
     * @throws TransferException
     * @throws ConnectionException
     */
    public function send($notifiable, TransferReceivedNotification $notification): void
    {
        $payload = $notification->toCustom($notifiable);

        $response = Http::post(config('services.notifier.url'), $payload);

        if ($response->failed()) {
            throw new TransferException('Failed to send notification.', 500);
        }
    }
}
