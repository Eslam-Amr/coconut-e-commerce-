<?php

namespace App\Notifications;

use App\Models\ProductVariant;
use App\Notifications\Contracts\RedisNotificationInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue, RedisNotificationInterface
{
    use Queueable;

    protected $productVariant;
    protected $channels;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProductVariant $productVariant, array $channels = ['database'])
    {
        $this->productVariant = $productVariant;
        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'product_variant_id' => $this->productVariant->id,
            'product_id' => $this->productVariant->product_id,
            'product_name' => $this->productVariant->product->name ?? 'Unknown Product',
            'variant_sku' => $this->productVariant->sku,
            'current_stock' => $this->productVariant->stock,
            'minimum_stock' => $this->productVariant->minimum_stock,
            'message' => "Low stock alert: {$this->productVariant->product->name} (SKU: {$this->productVariant->sku}) has only {$this->productVariant->stock} items left. Minimum stock is {$this->productVariant->minimum_stock}.",
            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get the Redis representation of the notification.
     */
    public function toRedis(object $notifiable): array
    {
        return [
            // 'channel' => 'admin-notifications',
            'channel' => 'laravel-events',
            'type' => 'low_stock',
            'product_variant_id' => $this->productVariant->id,
            'product_id' => $this->productVariant->product_id,
            'product_name' => $this->productVariant->product->name ?? 'Unknown Product',
            'variant_sku' => $this->productVariant->sku,
            'current_stock' => $this->productVariant->stock,
            'minimum_stock' => $this->productVariant->minimum_stock,
            'message' => "Low stock alert: {$this->productVariant->product->name} (SKU: {$this->productVariant->sku}) has only {$this->productVariant->stock} items left. Minimum stock is {$this->productVariant->minimum_stock}.",
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
