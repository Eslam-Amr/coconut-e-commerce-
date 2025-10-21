<?php

namespace App\Services\Order\States;

use App\Models\Order;
use App\Services\Order\OrderStateInterface;

class RefundedOrderState implements OrderStateInterface
{
    public function changeStatus(Order $order, string $newStatus): bool
    {
        // Refunded orders cannot be changed to any other status
        return false;
    }

    public function getPossibleNextStatuses(): array
    {
        return [];
    }

    public function getStatusDisplayName(): string
    {
        return 'Refunded';
    }

    public function getStatusColor(): string
    {
        return 'gray';
    }
}
