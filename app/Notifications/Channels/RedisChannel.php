<?php

namespace App\Notifications\Channels;

use App\Notifications\Contracts\RedisNotificationInterface;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RedisChannel
{
    public function send($notifiable, Notification $notification): void
    {
        try {
            // Check if notification implements RedisNotificationInterface
            if (!$notification instanceof RedisNotificationInterface) {
                Log::warning('Notification does not support Redis channel', [
                    'notification_type' => get_class($notification),
                    'notifiable_id' => $notifiable->id ?? null,
                ]);
                return;
            }

            $data = $notification->toRedis($notifiable);

            Redis::publish(
                $data['channel'] ?? 'laravel-events',
                json_encode($data)
            );

            Log::info('Notification sent via Redis', [
                'notification_type' => $data['type'] ?? 'unknown',
                'channel' => $data['channel'] ?? 'laravel-events',
                'notifiable_id' => $notifiable->id ?? null,
                'notifiable_type' => get_class($notifiable),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification via Redis', [
                'error' => $e->getMessage(),
                'notification_type' => get_class($notification),
                'notifiable_id' => $notifiable->id ?? null,
            ]);
        }
    }
}
