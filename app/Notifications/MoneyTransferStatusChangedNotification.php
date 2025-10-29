<?php

namespace App\Notifications;

use App\Models\MoneyTransfer;
use App\Notifications\Contracts\RedisNotificationInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MoneyTransferStatusChangedNotification extends Notification implements ShouldQueue, RedisNotificationInterface
{
    use Queueable;

    public $channels;

    public function __construct(
        public MoneyTransfer $transfer,
        public string $oldStatus,
        public string $newStatus,
        $channels = ['database']
    ) {
        $this->channels = $channels;
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'money_transfer_status_changed',
            'transfer_id' => $this->transfer->id,
            'transfer_reference' => $this->transfer->transfer_reference,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'amount' => $this->transfer->amount,
            'created_at' => $this->transfer->created_at,
            'message' => "Your money transfer {$this->transfer->transfer_reference} status changed from {$this->oldStatus} to {$this->newStatus}",
        ];
    }

    public function toRedis(object $notifiable): array
    {
        return [
            'type' => 'money_transfer_status_changed',
            'transfer_id' => $this->transfer->id,
            'transfer_reference' => $this->transfer->transfer_reference,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'amount' => $this->transfer->amount,
            'created_at' => $this->transfer->created_at,
            'message' => "Your money transfer {$this->transfer->transfer_reference} status changed from {$this->oldStatus} to {$this->newStatus}",
            'channel' => 'laravel-events',
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => get_class($notifiable),
        ];
    }
}


