<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Contracts\RedisNotificationInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OrderStatusChangedNotification extends Notification implements ShouldQueue, RedisNotificationInterface
{
    use Queueable;

    public $channels;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
        $channels = ['database']
    ) {        
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_status_changed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'total' => $this->order->total,
            'payment_status' => $this->order->payment_status,
            'updated_at' => $this->order->updated_at,
            'message' => "Your order #{$this->order->order_number} status has been changed from {$this->oldStatus} to {$this->newStatus}",
        ];
    }

    /**
     * Get the Redis representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toRedis(object $notifiable): array
    {
        return [
            'type' => 'order_status_changed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'total' => $this->order->total,
            'payment_status' => $this->order->payment_status,
            'updated_at' => $this->order->updated_at,
            'message' => "Your order #{$this->order->order_number} status has been changed from {$this->oldStatus} to {$this->newStatus}",
            // 'channel' => 'user-notifications',
            'channel' => 'laravel-events',
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => get_class($notifiable),
        ];
    }
}
