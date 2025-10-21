<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Services\Order\States\CancelledOrderState;
use App\Services\Order\States\ConfirmedOrderState;
use App\Services\Order\States\DeliveredOrderState;
use App\Services\Order\States\PendingOrderState;
use App\Services\Order\States\ProcessingOrderState;
use App\Services\Order\States\RefundedOrderState;
use App\Services\Order\States\ShippedOrderState;

class OrderStateFactory
{
    /**
     * Create order state based on status
     */
    public static function create(string $status): OrderStateInterface
    {
        return match (OrderStatus::from($status)) {
            OrderStatus::PENDING => new PendingOrderState(),
            OrderStatus::CONFIRMED => new ConfirmedOrderState(),
            OrderStatus::PROCESSING => new ProcessingOrderState(),
            OrderStatus::SHIPPED => new ShippedOrderState(),
            OrderStatus::DELIVERED => new DeliveredOrderState(),
            OrderStatus::CANCELLED => new CancelledOrderState(),
            OrderStatus::REFUNDED => new RefundedOrderState(),
        };
    }

    /**
     * Get all possible statuses
     */
    public static function getAllStatuses(): array
    {
        return OrderStatus::values();
    }

    /**
     * Get status with display information
     */
    public static function getStatusInfo(string $status): array
    {
        $state = self::create($status);
        $statusEnum = OrderStatus::from($status);
        
        return [
            'value' => $status,
            'display_name' => $state->getStatusDisplayName(),
            'color' => $state->getStatusColor(),
            'possible_next' => $state->getPossibleNextStatuses(),
        ];
    }
}
