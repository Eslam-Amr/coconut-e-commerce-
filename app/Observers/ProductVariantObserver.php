<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Models\Admin;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "updated" event.
     */
    public function updated(ProductVariant $productVariant): void
    {
        // Check if stock was changed and reached minimum stock
        if ($productVariant->wasChanged('stock') && 
            $productVariant->stock <= $productVariant->minimum_stock) {
            
            $this->notifyAdminsAboutLowStock($productVariant);
        }
    }

    /**
     * Notify super admins about low stock
     */
    private function notifyAdminsAboutLowStock(ProductVariant $productVariant): void
    {
        try {
            $productVariant->load('product');
            
            // Get super admin users
            $superAdmins = Admin::whereHas('role', function ($query) {
                $query->where('name', 'super_admin');
            })->get();

            if ($superAdmins->isNotEmpty()) {
                // Send notification to each admin with their preferred channels
                foreach ($superAdmins as $admin) {
                    $channels = ['database'];
                    
                    // Add Redis channel if admin has notification enabled
                    if ($admin->notification == 1) {
                        $channels[] = 'redis';
                    }
                    
                    $admin->notify(new LowStockNotification($productVariant, $channels));
                }

                $redisAdminCount = $superAdmins->filter(function ($admin) {
                    return $admin->notification == 1;
                })->count();

                Log::info('Low stock notification sent to super admins', [
                    'product_variant_id' => $productVariant->id,
                    'product_id' => $productVariant->product_id,
                    'product_name' => $productVariant->product->name ?? 'Unknown',
                    'variant_sku' => $productVariant->sku,
                    'current_stock' => $productVariant->stock,
                    'minimum_stock' => $productVariant->minimum_stock,
                    'admin_count' => $superAdmins->count(),
                    'redis_admin_count' => $redisAdminCount
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about low stock', [
                'product_variant_id' => $productVariant->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
