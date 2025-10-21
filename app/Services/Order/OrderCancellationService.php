<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantInventory;
use App\Models\RefundedMoney;
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
                'error' => $e->getTraceAsString()
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
                if ($variant) {
                    // Restore variant inventory
                    ProductVariantInventory::where('product_variant_id', $variant->id)
                        ->increment('quantity', $item->quantity);
                }
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

        switch ($paymentMethod) {
            case 'wallet':
                $this->refundToWallet($order, $amount, $reason);
                break;
            
            case 'visa':
            case 'payment_gateway':
                $this->createRefundRecord($order, $amount, $paymentMethod, $reason);
                break;
            
            case 'cash':
            case 'cash_on_delivery':
                // No refund needed for cash payments
                break;
            
            default:
                // Create refund record for unknown payment methods
                $this->createRefundRecord($order, $amount, $paymentMethod, $reason);
                break;
        }
    }

    /**
     * Refund amount to user's wallet
     */
    private function refundToWallet(Order $order, float $amount, string $reason): void
    {
        $user = $order->user;
        
        // Get or create user's wallet
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // Add amount to wallet balance
        $wallet->increment('balance', $amount);

        // Create wallet transaction record
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'refund',
            'amount' => $amount,
            'description' => "Order cancellation refund - Order #{$order->order_number}",
            'reference_id' => $order->id,
            'reference_type' => Order::class,
        ]);

        // Create refunded money record
        RefundedMoney::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => 'wallet',
            'reason' => $reason,
            'status' => 'completed',
            'notes' => 'Refunded to user wallet',
            'processed_at' => now(),
        ]);
    }

    /**
     * Create refund record for external payment methods
     */
    private function createRefundRecord(Order $order, float $amount, string $paymentMethod, string $reason): void
    {
        RefundedMoney::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'reason' => $reason,
            'status' => 'pending',
            'notes' => 'Refund processing required',
        ]);
    }

    /**
     * Get refund statistics
     */
    public function getRefundStats(): array
    {
        return [
            'total_refunds' => RefundedMoney::count(),
            'pending_refunds' => RefundedMoney::where('status', 'pending')->count(),
            'completed_refunds' => RefundedMoney::where('status', 'completed')->count(),
            'failed_refunds' => RefundedMoney::where('status', 'failed')->count(),
            'total_refunded_amount' => RefundedMoney::where('status', 'completed')->sum('amount'),
            'pending_refunded_amount' => RefundedMoney::where('status', 'pending')->sum('amount'),
        ];
    }

    /**
     * Update refund status
     */
    public function updateRefundStatus(int $refundId, string $status, string $notes = null): bool
    {
        try {
            $refund = RefundedMoney::findOrFail($refundId);
            
            $updateData = ['status' => $status];
            
            if ($status === 'completed') {
                $updateData['processed_at'] = now();
            }
            
            if ($notes) {
                $updateData['notes'] = $notes;
            }
            
            $refund->update($updateData);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update refund status: ' . $e->getMessage());
            return false;
        }
    }
}
