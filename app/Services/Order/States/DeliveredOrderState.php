<?php

namespace App\Services\Order\States;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Order\OrderStateInterface;

class DeliveredOrderState implements OrderStateInterface
{
    public function changeStatus(Order $order, string $newStatus): bool
    {
        $newStatusEnum = OrderStatus::from($newStatus);
        
        if (!$this->canTransitionTo($newStatusEnum)) {
            return false;
        }

        $order->update(['status' => $newStatus]);
        return true;
    }

    public function getPossibleNextStatuses(): array
    {
        return [];
    }

    public function getStatusDisplayName(): string
    {
        return 'Delivered';
    }

    public function getStatusColor(): string
    {
        return 'green';
    }

    private function canTransitionTo(OrderStatus $newStatus): bool
    {
        return false; // Delivered orders cannot be changed to any other status
    }
}
