<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\Utilities\InteractionPointsService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function __construct(private InteractionPointsService $interactionService)
    {
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if payment status changed to completed
        if ($order->wasChanged('payment_status') && $order->payment_status === 'completed') {
            $this->recordPurchaseInteractions($order);
        }
    }

    /**
     * Record purchase interactions for completed orders
     */
    private function recordPurchaseInteractions(Order $order): void
    {
        try {
            // Load order items with products
            $order->load('items');
            
            foreach ($order->items as $item) {
                $this->interactionService->recordInteraction(
                    $order->user_id,
                    $item->product_id,
                    'purchase'
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to record purchase interactions in observer', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}