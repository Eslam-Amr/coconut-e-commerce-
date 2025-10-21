<?php

namespace App\Services\Order\States;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Order\OrderCancellationService;
use App\Services\Order\OrderStateInterface;

class ShippedOrderState implements OrderStateInterface
{
    public function changeStatus(Order $order, string $newStatus): bool
    {
        $newStatusEnum = OrderStatus::from($newStatus);
        
        if (!$this->canTransitionTo($newStatusEnum)) {
            return false;
        }

        // Handle cancellation logic
        if ($newStatusEnum === OrderStatus::CANCELLED) {
            $cancellationService = new OrderCancellationService();
            if (!$cancellationService->handleCancellation($order, 'Order cancelled by admin')) {
                return false;
            }
        }

        $order->update(['status' => $newStatus]);
        return true;
    }

    public function getPossibleNextStatuses(): array
    {
        return [
            OrderStatus::DELIVERED->value,
            OrderStatus::CANCELLED->value,
        ];
    }

    public function getStatusDisplayName(): string
    {
        return 'Shipped';
    }

    public function getStatusColor(): string
    {
        return 'orange';
    }

    private function canTransitionTo(OrderStatus $newStatus): bool
    {
        return in_array($newStatus, [
            OrderStatus::DELIVERED,
            OrderStatus::CANCELLED,
        ]);
    }
}
