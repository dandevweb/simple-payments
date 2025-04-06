<?php

namespace App\Broadcasting;

use App\Exceptions\TransferException;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class CustomNotificationChannel
{
    /**
     * @throws TransferException
     * @throws ConnectionException
     */
    public function send($notifiable, Notification $notification): void
    {
        $payload = $notification->toCustom($notifiable);

        $response = Http::post(config('services.notifier.url'), $payload);

        if ($response->failed()) {
            throw new TransferException('Failed to send notification.', 500);
        }
    }
}
