<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    /**
     * Get all status values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get status display name
     */
    public function getDisplayName(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get status color for UI
     */
    public function getColor(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::CONFIRMED => 'blue',
            self::PROCESSING => 'purple',
            self::SHIPPED => 'orange',
            self::DELIVERED => 'green',
            self::CANCELLED => 'red',
        };
    }

    /**
     * Check if status can be changed to another status
     */
    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return match($this) {
            self::PENDING => in_array($newStatus, [self::CONFIRMED, self::CANCELLED]),
            self::CONFIRMED => in_array($newStatus, [self::PROCESSING, self::CANCELLED]),
            self::PROCESSING => in_array($newStatus, [self::SHIPPED, self::CANCELLED]),
            self::SHIPPED => in_array($newStatus, [self::DELIVERED, self::CANCELLED]),
            self::DELIVERED => false, // Delivered orders cannot be changed
            self::CANCELLED => false, // Cannot change from cancelled
        };
    }

    /**
     * Get next possible statuses
     */
    public function getNextPossibleStatuses(): array
    {
        return match($this) {
            self::PENDING => [self::CONFIRMED, self::CANCELLED],
            self::CONFIRMED => [self::PROCESSING, self::CANCELLED],
            self::PROCESSING => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED => [self::DELIVERED, self::CANCELLED],
            self::DELIVERED => [],
            self::CANCELLED => [],
        };
    }
}
