<?php

namespace App\Notifications\Contracts;

interface RedisNotificationInterface
{
    /**
     * Get the Redis representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toRedis(object $notifiable): array;
}
