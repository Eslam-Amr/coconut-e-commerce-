<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Admin;
use App\Models\User;
use App\Services\Utilities\InteractionPointsService;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderObserver
{
    public function __construct(private InteractionPointsService $interactionService)
    {
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $this->notifyAdminsAboutNewOrder($order);
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

        // Check if order status changed
        if ($order->wasChanged('status')) {
            $this->notifyUserAboutStatusChange($order);
        }
    }

    /**
     * Notify super_admin users about new order creation
     */
    private function notifyAdminsAboutNewOrder(Order $order): void
    {
        try {
            // Load user relationship
            $order->load('user');

            // Get all super_admin users
            $superAdmins = Admin::whereHas('role', function ($query) {
                $query->where('name', 'super_admin');
            })->get();

            if ($superAdmins->isNotEmpty()) {
                // Send notification to all super_admin users
                // Notification::send($superAdmins, new OrderCreatedNotification($order, ['database']));
                Notification::send($superAdmins, new OrderCreatedNotification($order, ['database', 'redis']));
                
                Log::info('Order creation notification sent to super admins', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_name' => $order->user->name,
                    'admin_count' => $superAdmins->count()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about new order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify user about order status change
     */
    private function notifyUserAboutStatusChange(Order $order): void
    {
        try {
            // Load user relationship
            $order->load('user');

            if (!$order->user) {
                Log::warning('Cannot notify user about status change - user not found', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id
                ]);
                return;
            }

            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status->value;
            
            // Convert oldStatus to string if it's an enum
            if ($oldStatus instanceof \App\Enums\OrderStatus) {
                $oldStatus = $oldStatus->value;
            }

            // Determine notification channels based on user preference
            $channels = ['database'];
            
            // If user has notification preference enabled, add Redis channel
            if ($order->user->notification) {
                $channels[] = 'redis';
            }

            // Send notification to user
            $order->user->notify(new OrderStatusChangedNotification(
                $order,
                $oldStatus,
                $newStatus,
                $channels
            ));

            Log::info('Order status change notification sent to user', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $order->user_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'channels' => $channels
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify user about status change', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'error' => $e->getMessage()
            ]);
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