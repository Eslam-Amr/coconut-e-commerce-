<?php

namespace App\Notifications;

use App\Models\MoneyTransfer;
use App\Notifications\Contracts\RedisNotificationInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MoneyTransferCreatedNotification extends Notification implements ShouldQueue, RedisNotificationInterface
{
    use Queueable;

    public $channels;

    public function __construct(public MoneyTransfer $transfer, $channels = ['database','redis'])
    {
        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    // No mail channel for now

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'money_transfer_created',
            'transfer_id' => $this->transfer->id,
            'transfer_reference' => $this->transfer->transfer_reference,
            'user_id' => $this->transfer->user_id,
            'amount' => $this->transfer->amount,
            'status' => $this->transfer->status,
            'bank_name' => $this->transfer->bank_name,
            'account_number' => $this->transfer->account_number,
            'iban' => $this->transfer->iban,
            'created_at' => $this->transfer->created_at,
            'message' => 'New money transfer request created',
        ];
    }

    public function toRedis(object $notifiable): array
    {
        return [
            'type' => 'money_transfer_created',
            'transfer_id' => $this->transfer->id,
            'transfer_reference' => $this->transfer->transfer_reference,
            'user_id' => $this->transfer->user_id,
            'amount' => $this->transfer->amount,
            'status' => $this->transfer->status,
            'bank_name' => $this->transfer->bank_name,
            'account_number' => $this->transfer->account_number,
            'iban' => $this->transfer->iban,
            'created_at' => $this->transfer->created_at,
            'message' => 'New money transfer request created',
            'channel' => 'laravel-events',
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => get_class($notifiable),
        ];
    }
}
