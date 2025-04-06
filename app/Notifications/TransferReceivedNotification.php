<?php

namespace App\Notifications;

use App\Broadcasting\CustomNotificationChannel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransferReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly float $value,
        private readonly string $payerName
    ) {}

    public function via(): array
    {
        return [CustomNotificationChannel::class];
    }

    public function toCustom(User $notifiable): array
    {
        return [
            'user_id' => $notifiable->id,
            'message' => "Você recebeu R$ {$this->value} de {$this->payerName}",
        ];
    }
}
