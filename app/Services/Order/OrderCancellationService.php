<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantInventory;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderCancellationService
{
    /**
     * Handle order cancellation with stock restoration and refunds
     */
    public function handleCancellation(Order $order, string $reason = 'Order cancelled'): bool
    {
        try {
            DB::beginTransaction();

            // Restore product stock
            $this->restoreProductStock($order);

            // Handle refunds based on payment method
            $this->handleRefunds($order, $reason);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Restore product and variant stock
     */
    private function restoreProductStock(Order $order): void
    {
        $orderItems = $order->items;

        foreach ($orderItems as $item) {
            // Restore main product stock
            if ($item->product_id) {
                Product::where('id', $item->product_id)
                    ->increment('total_quantity', $item->quantity);
            }

            // Restore variant stock if exists
            if ($item->product_variant_id) {
                $variant = ProductVariant::find($item->product_variant_id);
               $variant->increment('stock', $item->quantity);
            }
        }
    }

    /**
     * Handle refunds based on payment method
     */
    private function handleRefunds(Order $order, string $reason): void
    {
        $paymentMethod = $order->payment_method;
        $amount = $order->total;

        if($paymentMethod === 'payment_gateway' || $paymentMethod === 'wallet') 
            $this->refundToWallet($order, $amount, $reason);
         
    }

    /**
     * Refund amount to user's wallet
     */
    private function refundToWallet(Order $order, float $amount, string $reason): void
    {
        $user = $order->user;
        $paymentMethod = $order->payment_method;
        
        // Get or create user's wallet
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // Add amount to wallet balance
        $wallet->increment('balance', $amount);

        // Create appropriate description based on payment method
        $description = $paymentMethod === 'wallet' 
            ? "Order cancellation refund (Wallet) - Order #{$order->order_number}"
            : "Order cancellation refund (Payment Gateway) - Order #{$order->order_number}";

        // Create wallet transaction record
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'user_id' => $user->id,
            'transaction_id' => "REFUND-".time()."-".$order->id,
            'description' => $description,
            'reference_id' => $order->id,
            'reference_type' => Order::class,
        ]);

    }
}
