<?php

namespace App\Services\Api\App\Client\Order;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Models\Inventory;
use App\Models\ProductVariantInventory;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    use ApiResponseTrait;

    /**
     * Confirm order from cart
     */
    public function confirmOrder(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            // Get user's cart
            $cart = Cart::where('user_id', $user->id)->with(['items.product', 'items.productVariant', 'items.flashSale'])->first();
            
            if (!$cart || $cart->items->isEmpty()) {
                return $this->errorResponse('Cart is empty', [], 400);
            }

            // Validate flash sales and stock before creating order
            $validationResult = $this->validateCartForOrder($cart);
            if (!$validationResult['valid']) {
                return $this->errorResponse($validationResult['message'], $validationResult['details'], 400);
            }

            DB::beginTransaction();

            try {
                // Create order
                $order = $this->createOrder($user, $cart);
                
                // Create order items and update stock
                $this->createOrderItems($order, $cart);
                
                // Update flash sale counts
                $this->updateFlashSaleCounts($cart);
                
                // Clear cart
                $cart->items()->delete();
                $cart->delete();

                DB::commit();

                return $this->successResponse(
                    'Order confirmed successfully',
                    [
                        'order' => $order->load(['items.product', 'items.productVariant', 'items.flashSale']),
                        'order_number' => $order->order_number
                    ]
                );

            } catch (\Exception $e) {
                DB::rollBack();
                return $this->errorResponse('Failed to confirm order: ' . $e->getMessage(), [], 500);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process order confirmation', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validate cart items for order confirmation
     */
    private function validateCartForOrder(Cart $cart)
    {
        $errors = [];
        $now = now();

        foreach ($cart->items as $item) {
            // Check if flash sale is still active
            if ($item->flash_sale_id) {
                $flashSale = FlashSale::find($item->flash_sale_id);
                
                if (!$flashSale || !$flashSale->active || 
                    $flashSale->start_date > $now || 
                    $flashSale->end_date < $now) {
                    $errors[] = "Flash sale for product '{$item->product->name}' has expired";
                    continue;
                }

                // Check flash sale limits
                $flashSaleItem = FlashSaleItem::where('flash_sale_id', $flashSale->id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($flashSaleItem) {
                    $totalOrdered = OrderItem::whereHas('order', function($q) use ($flashSale) {
                        $q->where('created_at', '>=', $flashSale->start_date)
                          ->where('created_at', '<=', $flashSale->end_date);
                    })
                    ->where('product_id', $item->product_id)
                    ->where('flash_sale_id', $flashSale->id)
                    ->sum('quantity');

                    if (($totalOrdered + $item->quantity) > $flashSaleItem->max_limit) {
                        $available = $flashSaleItem->max_limit - $totalOrdered;
                        $errors[] = "Flash sale limit exceeded for '{$item->product->name}'. Available: {$available}";
                    }
                }
            }

            // Check stock availability
            if ($item->product_variant_id) {
                $variantInventory = ProductVariantInventory::where('product_variant_id', $item->product_variant_id)
                    ->where('quantity', '>=', $item->quantity)
                    ->first();

                if (!$variantInventory) {
                    $errors[] = "Insufficient stock for variant of '{$item->product->name}'";
                }
            } else {
                $inventory = Inventory::where('product_id', $item->product_id)
                    ->where('quantity', '>=', $item->quantity)
                    ->first();

                if (!$inventory) {
                    $errors[] = "Insufficient stock for '{$item->product->name}'";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'message' => empty($errors) ? 'Cart is valid' : 'Cart validation failed',
            'details' => $errors
        ];
    }

    /**
     * Create order record
     */
    private function createOrder($user, Cart $cart)
    {
        $subtotal = $cart->items->sum(function($item) {
            return $item->unit_price * $item->quantity;
        });

        return Order::create([
            'user_id' => $user->id,
            'order_number' => $this->generateOrderNumber(),
            'status' => 'pending',
            'subtotal' => $subtotal,
            'total' => $subtotal, // Will be updated with shipping, tax, etc.
            'currency' => 'USD',
            'payment_status' => 'pending',
            'shipping_status' => 'pending'
        ]);
    }

    /**
     * Create order items and update stock
     */
    private function createOrderItems(Order $order, Cart $cart)
    {
        foreach ($cart->items as $cartItem) {
            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'product_variant_id' => $cartItem->product_variant_id,
                'flash_sale_id' => $cartItem->flash_sale_id,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'total_price' => $cartItem->unit_price * $cartItem->quantity
            ]);

            // Update stock
            if ($cartItem->product_variant_id) {
                ProductVariantInventory::where('product_variant_id', $cartItem->product_variant_id)
                    ->decrement('quantity', $cartItem->quantity);
            } else {
                Inventory::where('product_id', $cartItem->product_id)
                    ->decrement('quantity', $cartItem->quantity);
            }
        }
    }

    /**
     * Update flash sale counts
     */
    private function updateFlashSaleCounts(Cart $cart)
    {
        foreach ($cart->items as $item) {
            if ($item->flash_sale_id) {
                FlashSaleItem::where('flash_sale_id', $item->flash_sale_id)
                    ->where('product_id', $item->product_id)
                    ->increment('sold_count', $item->quantity);
            }
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Get user orders
     */
    public function getUserOrders(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $orders = Order::where('user_id', $user->id)
                ->with(['items.product', 'items.productVariant'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return $this->successResponse('Orders retrieved successfully', $orders);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve orders', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get specific order details
     */
    public function getOrderDetails(Request $request, $orderId)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->with(['items.product', 'items.productVariant', 'items.flashSale'])
                ->first();

            if (!$order) {
                return $this->errorResponse('Order not found', [], 404);
            }

            return $this->successResponse('Order details retrieved successfully', $order);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve order details', ['error' => $e->getMessage()], 500);
        }
    }
}
