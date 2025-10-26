<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Contracts\RedisNotificationInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OrderCreatedNotification extends Notification implements ShouldQueue, RedisNotificationInterface
{
    use Queueable;

    public $channels;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order,
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
            'type' => 'order_created',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'user_name' => $this->order->user->name,
            'user_id' => $this->order->user_id,
            'total' => $this->order->total,
            'status' => $this->order->status->value,
            'payment_status' => $this->order->payment_status,
            'created_at' => $this->order->created_at,
            'message' => "New order #{$this->order->order_number} created by {$this->order->user->name}",
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
            'type' => 'order_created',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'user_name' => $this->order->user->name,
            'user_id' => $this->order->user_id,
            'total' => $this->order->total,
            'status' => $this->order->status->value,
            'payment_status' => $this->order->payment_status,
            'created_at' => $this->order->created_at,
            'message' => "New order #{$this->order->order_number} created by {$this->order->user->name}",
            // 'channel' => 'admin-notifications',
            'channel' => 'laravel-events',
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => get_class($notifiable),
        ];
    }
}
