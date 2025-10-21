<?php

namespace App\Services\Order\States;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Order\OrderCancellationService;
use App\Services\Order\OrderStateInterface;

class PendingOrderState implements OrderStateInterface
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
            OrderStatus::CONFIRMED->value,
            OrderStatus::CANCELLED->value,
        ];
    }

    public function getStatusDisplayName(): string
    {
        return 'Pending';
    }

    public function getStatusColor(): string
    {
        return 'yellow';
    }

    private function canTransitionTo(OrderStatus $newStatus): bool
    {
        return in_array($newStatus, [
            OrderStatus::CONFIRMED,
            OrderStatus::CANCELLED,
        ]);
    }
}
