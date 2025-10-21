<?php

namespace App\Services\Order;

use App\Models\Order;

interface OrderStateInterface
{
    /**
     * Change order status
     */
    public function changeStatus(Order $order, string $newStatus): bool;

    /**
     * Get possible next statuses
     */
    public function getPossibleNextStatuses(): array;

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string;

    /**
     * Get status color
     */
    public function getStatusColor(): string;
}
