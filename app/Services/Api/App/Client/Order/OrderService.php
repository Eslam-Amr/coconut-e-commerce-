<?php

namespace App\Services\Api\App\Client\Order;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\FlashSale;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Utilities\OrderPaymentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderService
{
    use ApiResponseTrait;

    protected $paymentService;

    public function __construct(OrderPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Confirm order from cart
     */
    public function confirmOrder(Request $request)
    {
        try {
            $user = $request->user();

            // Get user's cart
            $cart = Cart::where('user_id', $user->id)->with(['items.product', 'items.productVariant', 'items.flashSale'])->first();

            if (!$cart || $cart->items->isEmpty()) {
                return $this->errorResponse(__('messages.cart_empty'), [], 400);
            }

            // Get voucher validation data
            $voucherValidation = $this->getVoucherValidation($request->voucher_code, $user->id, $cart);

            // Get payment validation data
            $paymentValidation = $this->getPaymentValidation($request->payment_method, $user->id, $cart, $voucherValidation['discount'] ?? 0);

            // Handle payment gateway separately
            if ($request->payment_method === 'payment_gateway') {
                return $this->processPaymentGatewayOrder($user, $cart, $request, $voucherValidation, $paymentValidation);
            }

            DB::beginTransaction();

            try {
                // Calculate paid amount once at the beginning
                $paidAmount = $this->calculatePaidAmount($cart, $voucherValidation['discount'] ?? 0);
                
                // Create order
                $order = $this->createOrder($user, $cart, $request, $voucherValidation['discount'] ?? 0);

                // Create order items and update stock
                $this->createOrderItems($order, $cart);

                // Create transaction record with pre-calculated paid amount
                $this->createTransaction($order, $request->payment_method, $paymentValidation, null, $cart, $paidAmount);

                // Process voucher usage if applicable
                if ($request->voucher_code && $voucherValidation['voucher']) {
                    $this->processVoucherUsage($voucherValidation['voucher'], $user->id, $order->id, $voucherValidation['discount']);
                }

                // Process wallet payment if applicable
                if ($request->payment_method === 'wallet') {
                    $this->processWalletPayment($user->id, $paidAmount);
                }

                // Update flash sale counts
                $this->updateFlashSaleCounts($cart);

                // Clear cart
                $cart->items()->delete();
                $cart->delete();

                DB::commit();

                return $this->successResponse(
                    __('messages.order_confirmed'),
                    [
                        'order' => $order->load(['items.product', 'items.productVariant', 'items.flashSale', 'transactions']),
                        'order_number' => $order->order_number,
                        'transaction_id' => $order->transactions->first()->transaction_id ?? null
                    ]
                );
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->errorResponse(__('messages.order_confirmation_failed') . ': ' . $e->getMessage(), [], 500);
            }
        } catch (\Exception $e) {
            return $this->errorResponse(__('messages.order_processing_failed'), ['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Create order record
     */
    private function createOrder($user, Cart $cart, Request $request, $discount = 0, $paymentStatus = null)
    {
        $subtotal = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $addressId = $request->address_id ?? $user->default_address_id;
        $address = Address::find($addressId);
        $longitude = $address->longitude;
        $latitude = $address->latitude;

        $shippingPrice = $this->calculateShippingCost($latitude, $longitude);
        
        // Get settings for tax and VAT rates
        $settings = Settings::current();
        
        // Calculate tax and VAT on subtotal (before discount)
        $taxAmount = $subtotal * $settings->tax_rate / 100;
        $vatAmount = $subtotal * $settings->vat_rate / 100;
        
        // Calculate final total: subtotal - discount + shipping + tax + VAT
        $total = ($subtotal - $discount) + $shippingPrice + $taxAmount + $vatAmount;

        // Determine payment status
        if ($paymentStatus) {
            $finalPaymentStatus = $paymentStatus;
        } else {
            $finalPaymentStatus = $request->payment_method === 'cash' ? 'pending' : 'completed';
        }

        return Order::create([
            'user_id' => $user->id,
            'address_id' => $addressId,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'order_number' => $this->generateOrderNumber(),
            'status' => 'pending',
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping_price' => $shippingPrice,
            'tax' => $taxAmount,
            'vat' => $vatAmount,
            'total' => $total, // Complete total including shipping, tax, and VAT
            'payment_method' => $request->payment_method,
            'payment_status' => $finalPaymentStatus,
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
                'price' => $cartItem->price,
                // 'total_price' => $cartItem->price * $cartItem->quantity
            ]);

            // Update stock
            if ($cartItem->product_variant_id) {
                // Update variant stock from product_variants table
                $variant = ProductVariant::find($cartItem->product_variant_id);
                if ($variant && $variant->stock >= $cartItem->quantity) {
                    $oldStock = $variant->stock;
                    $variant->decrement('stock', $cartItem->quantity);

                    // Log::info('Variant stock updated', [
                    //     'variant_id' => $variant->id,
                    //     'product_id' => $cartItem->product_id,
                    //     'old_stock' => $oldStock,
                    //     'quantity_decremented' => $cartItem->quantity,
                    //     'new_stock' => $variant->fresh()->stock
                    // ]);
                    // Update regular product stock from products table
                    $product = Product::find($cartItem->product_id);
                    if ($product && $product->total_quantity >= $cartItem->quantity) {
                        $oldStock = $product->total_quantity;
                        $product->decrement('total_quantity', $cartItem->quantity);

                        // Log::info('Product stock updated', [
                        //     'product_id' => $cartItem->product_id,
                        //     'old_stock' => $oldStock,
                        //     'quantity_decremented' => $cartItem->quantity,
                        //     'new_stock' => $product->fresh()->total_quantity
                        // ]);
                    } else {
                        throw new \Exception("Insufficient stock for product ID: {$cartItem->product_id}");
                    }
                } else {
                    throw new \Exception("Insufficient stock for variant of product ID: {$cartItem->product_id}");
                }
            } else {
                // Update regular product stock from products table
                $product = Product::find($cartItem->product_id);
                if ($product && $product->total_quantity >= $cartItem->quantity) {
                    $oldStock = $product->total_quantity;
                    $product->decrement('total_quantity', $cartItem->quantity);

                    // Log::info('Product stock updated', [
                    //     'product_id' => $cartItem->product_id,
                    //     'old_stock' => $oldStock,
                    //     'quantity_decremented' => $cartItem->quantity,
                    //     'new_stock' => $product->fresh()->total_quantity
                    // ]);
                } else {
                    throw new \Exception("Insufficient stock for product ID: {$cartItem->product_id}");
                }
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
                // Update the count directly in the flash_sales table
                FlashSale::where('id', $item->flash_sale_id)
                    ->increment('count', $item->quantity);
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
                return $this->errorResponse(__('messages.unauthorized'), [], 401);
            }

            $orders = Order::where('user_id', $user->id)
                ->with(['items.product', 'items.productVariant'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return $this->successResponse(__('messages.retrieved_successfully'), $orders);
        } catch (\Exception $e) {
            return $this->errorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()], 500);
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
                return $this->errorResponse(__('messages.unauthorized'), [], 401);
            }

            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->with(['items.product', 'items.productVariant', 'items.flashSale'])
                ->first();

            if (!$order) {
                return $this->errorResponse(__('messages.not_found'), [], 404);
            }

            return $this->successResponse(__('messages.retrieved_successfully'), $order);
        } catch (\Exception $e) {
            return $this->errorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get voucher validation data
     */
    private function getVoucherValidation($voucherCode, $userId, Cart $cart)
    {
        if (!$voucherCode) {
            return ['valid' => true, 'discount' => 0];
        }

        $voucher = Voucher::where('code', $voucherCode)
            ->where('active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$voucher) {
            return ['valid' => false, 'message' => 'Invalid or expired voucher code'];
        }

        // Calculate discount amount
        $subtotal = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $discount = min($voucher->discount, $subtotal);

        return [
            'valid' => true,
            'voucher' => $voucher,
            'discount' => $discount
        ];
    }

    /**
     * Get payment validation data
     */
    private function getPaymentValidation($paymentMethod, $userId, Cart $cart, $discount = 0)
    {
        $subtotal = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $total = $subtotal - $discount;

        return [
            'valid' => true,
            'amount' => $total
        ];
    }

    /**
     * Calculate complete order total including shipping, tax, and VAT
     */
    private function calculatePaidAmount(Cart $cart, $discount = 0, Order $order = null)
    {
        $subtotal = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Get shipping price - use order's shipping price if available, otherwise calculate
        if ($order) {
            $shippingPrice = $order->shipping_price ?? 0;
        } else {
            // Calculate shipping price for cart
            $user = Auth::user();
            $addressId = request()->address_id ?? $user->default_address_id ?? null;
            
            if ($addressId) {
                $address = \App\Models\Address::find($addressId);
                $shippingPrice = $this->calculateShippingCost($address->latitude ?? null, $address->longitude ?? null);
            } else {
                $shippingPrice = 0;
            }
        }
        
        // Get settings for tax and VAT rates
        $settings = Settings::current();
        
        // Calculate tax and VAT on subtotal (before discount)
        $taxAmount = $subtotal * $settings->tax_rate / 100;
        $vatAmount = $subtotal * $settings->vat_rate / 100;
        
        // Calculate paid amount: subtotal - discount + shipping + tax + VAT
        $paidAmount = ($subtotal - $discount) + $shippingPrice + $taxAmount + $vatAmount;
        
        return round($paidAmount, 2);
    }

    /**
     * Create transaction record
     */
    private function createTransaction(Order $order, $paymentMethod, $paymentValidation, $status = null, Cart $cart = null, $paidAmount = null)
    {
        $setting = Settings::current();
        
        // Determine transaction ID based on payment method
        if ($paymentMethod === 'payment_gateway') {
            // For payment gateway, use the actual Stripe session ID or transaction ID
            $transactionId = $paymentValidation['stripe_session_id'] ?? $paymentValidation['transaction_id'] ?? 'PENDING-' . time();
        } else {
            // For other payment methods, generate custom transaction ID
            $transactionId = 'TXN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
        }
        
        // Determine transaction status
        if ($status) {
            $finalStatus = $status;
        } else {
            if ($paymentMethod === 'cash') {
                $finalStatus = 'pending'; // Cash payments are pending until confirmed
            } else {
                $finalStatus = 'completed'; // Wallet and payment gateway are completed immediately
            }
        }

        // Use provided paid amount or calculate if not provided
        if ($paidAmount === null) {
            $paidAmount = $cart ? $this->calculatePaidAmount($cart, $order->discount ?? 0, $order) : $paymentValidation['amount'];
        }

        // Calculate tax and VAT on the order subtotal (before discount)
        $orderSubtotal = $order->subtotal;
        $taxAmount = $orderSubtotal * $setting->tax_rate / 100;
        $vatAmount = $orderSubtotal * $setting->vat_rate / 100;

        Transaction::create([
            'transaction_id' => $transactionId,
            'order_id' => $order->id,
            'amount' => $order->subtotal - $order->discount, // Order amount after discount (without shipping/tax/VAT)
            'paid_amount' => $paidAmount, // Complete amount to be paid (including everything)
            'status' => $finalStatus,
            'payment_method' => $this->mapPaymentMethod($paymentMethod),
            'tax' => $taxAmount,
            'vat' => $vatAmount,
            'tax_percentage' => $setting->tax_rate,
            'vat_percentage' => $setting->vat_rate,
        ]);
    }

    /**
     * Map payment method to transaction enum
     */
    private function mapPaymentMethod($paymentMethod)
    {
        $mapping = [
            'cash' => 'cash_on_delivery',
            'wallet' => 'wallet',
            'payment_gateway' => 'credit_card'
        ];

        return $mapping[$paymentMethod] ?? 'cash_on_delivery';
    }

    /**
     * Process voucher usage
     */
    private function processVoucherUsage(Voucher $voucher, $userId, $orderId, $discountAmount)
    {
        // Create voucher usage record
        VoucherUsage::create([
            'voucher_id' => $voucher->id,
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount
        ]);

        // Update voucher used count
        $voucher->increment('used_count');
    }

    /**
     * Process wallet payment
     */
    private function processWalletPayment($userId, $amount)
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        if ($wallet) {
            // Deduct amount from wallet
            $oldBalance = $wallet->balance;
            if ($oldBalance < $amount) {
                return $this->errorResponse('Insufficient wallet balance', [], 400);
            }
            $wallet->decrement('balance', $amount);
            // $wallet->update(['last_change' => -$amount]);

            // Create wallet transaction record
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $userId,
                // 'type' => 'debit',
                'amount' => $amount,
                'transaction_id' => 'PAY-' . $wallet->id . '-' . time(),
                'description' => 'Order payment',
                'balance_after' => $wallet->fresh()->balance
            ]);
        }
    }

    /**
     * Process payment gateway order
     */
    private function processPaymentGatewayOrder($user, $cart, $request, $voucherValidation, $paymentValidation)
    {
        try {
            // Calculate paid amount once at the beginning
            $paidAmount = $this->calculatePaidAmount($cart, $voucherValidation['discount'] ?? 0);
            
            // Create order with completed payment status for payment gateway
            $order = $this->createOrder($user, $cart, $request, $voucherValidation['discount'] ?? 0, 'completed');

            // Log::info('Payment gateway order created', [
            //     'order_id' => $order->id,
            //     'order_number' => $order->order_number,
            //     'payment_status' => $order->payment_status,
            //     'total' => $order->total
            // ]);

            // Create order items and update stock
            $this->createOrderItems($order, $cart);

            // Create transaction record with pre-calculated paid amount
            $this->createTransaction($order, $request->payment_method, $paymentValidation, null, $cart, $paidAmount);

            // Process voucher usage if applicable
            if ($request->voucher_code && $voucherValidation['voucher']) {
                $this->processVoucherUsage($voucherValidation['voucher'], $user->id, $order->id, $voucherValidation['discount']);
            }

            // Update flash sale counts
            $this->updateFlashSaleCounts($cart);

            // Clear cart
            $cart->items()->delete();
            $cart->delete();

            // Prepare payment request
            $paymentRequest = new Request([
                'amount' => $paidAmount, // Use paid_amount (includes shipping, tax, VAT)
                'currency' => $request->currency ?? 'USD',
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

            // Process payment through gateway
            $paymentResult = $this->paymentService->sendPayment($paymentRequest);

            // Log::info('Payment gateway result', [
            //     'order_id' => $order->id,
            //     'payment_success' => $paymentResult['success'],
            //     'payment_url' => $paymentResult['url'] ?? null,
            //     'stripe_session_id' => $paymentResult['session_id'] ?? null
            // ]);

            // Update transaction with Stripe session ID if available
            if ($paymentResult['success'] && isset($paymentResult['session_id'])) {
                $order->transactions()->update([
                    'transaction_id' => $paymentResult['session_id']
                ]);
                
                // Log::info('Transaction updated with Stripe session ID', [
                //     'order_id' => $order->id,
                //     'stripe_session_id' => $paymentResult['session_id']
                // ]);
            }

            if ($paymentResult['success']) {
                return $this->successResponse('Payment initiated successfully', [
                    'payment_url' => $paymentResult['url'],
                    'order' => $order->load(['items.product', 'items.productVariant', 'items.flashSale']),
                    'order_number' => $order->order_number,
                    'order_id' => $order->id
                ]);
            } else {
                // Payment initiation failed - restore stock and mark as failed
                $this->handleOrderFailure($order->id, 'Payment initiation failed');

                // Log::error('Payment gateway initiation failed', [
                //     'order_id' => $order->id,
                //     'order_number' => $order->order_number
                // ]);

                return $this->errorResponse('Payment initiation failed', [
                    'order_number' => $order->order_number
                ], 400);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process payment gateway order: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Handle order failure and restore stock
     */
    public function handleOrderFailure($orderId, $reason = 'Order failed')
    {
        try {
            $order = Order::find($orderId);
            if (!$order) {
                return false;
            }

            DB::beginTransaction();

            try {
                // Restore stock for all order items
                foreach ($order->items as $orderItem) {
                    if ($orderItem->product_variant_id) {
                        // Restore variant stock
                        $variant = ProductVariant::find($orderItem->product_variant_id);
                        if ($variant) {
                            $variant->increment('stock', $orderItem->quantity);

                            Log::info('Variant stock restored due to order failure', [
                                'variant_id' => $variant->id,
                                'product_id' => $orderItem->product_id,
                                'quantity_restored' => $orderItem->quantity,
                                'new_stock' => $variant->fresh()->stock,
                                'order_id' => $orderId,
                                'reason' => $reason
                            ]);
                        }
                    } else {
                        // Restore regular product stock
                        $product = Product::find($orderItem->product_id);
                        if ($product) {
                            $product->increment('total_quantity', $orderItem->quantity);

                            Log::info('Product stock restored due to order failure', [
                                'product_id' => $orderItem->product_id,
                                'quantity_restored' => $orderItem->quantity,
                                'new_stock' => $product->fresh()->total_quantity,
                                'order_id' => $orderId,
                                'reason' => $reason
                            ]);
                        }
                    }

                    // Restore flash sale counts if applicable
                    if ($orderItem->flash_sale_id) {
                        $flashSale = FlashSale::find($orderItem->flash_sale_id);
                        
                        if ($flashSale) {
                            $flashSale->decrement('count', $orderItem->quantity);

                            Log::info('Flash sale count restored due to order failure', [
                                'flash_sale_id' => $orderItem->flash_sale_id,
                                'product_id' => $orderItem->product_id,
                                'quantity_restored' => $orderItem->quantity,
                                'new_count' => $flashSale->fresh()->count,
                                'order_id' => $orderId,
                                'reason' => $reason
                            ]);
                        }
                    }
                }

                // Restore voucher usage if applicable
                $voucherUsages = VoucherUsage::where('order_id', $orderId)->get();
                foreach ($voucherUsages as $voucherUsage) {
                    // Decrement voucher used count
                    $voucher = Voucher::find($voucherUsage->voucher_id);
                    if ($voucher) {
                        $voucher->decrement('used_count');

                        Log::info('Voucher usage restored due to order failure', [
                            'voucher_id' => $voucher->id,
                            'voucher_code' => $voucher->code,
                            'order_id' => $orderId,
                            'new_used_count' => $voucher->fresh()->used_count,
                            'reason' => $reason
                        ]);
                    }

                    // Delete voucher usage record
                    $voucherUsage->delete();

                    Log::info('Voucher usage record deleted due to order failure', [
                        'voucher_usage_id' => $voucherUsage->id,
                        'voucher_id' => $voucherUsage->voucher_id,
                        'order_id' => $orderId,
                        'reason' => $reason
                    ]);
                }

                // Update order status
                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled'
                ]);

                // Restore wallet balance if order was paid with wallet
                if ($order->payment_method === 'wallet') {
                    $wallet = Wallet::where('user_id', $order->user_id)->first();
                    if ($wallet) {
                        $oldBalance = $wallet->balance;
                        $wallet->increment('balance', $order->total);

                        // Create wallet transaction record for refund
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => 'credit',
                            'amount' => $order->total,
                            'transaction_id' => 'REFUND-' . $wallet->id . '-' . time(),
                            'description' => 'Order refund due to failure: ' . $reason,
                            'balance_after' => $wallet->fresh()->balance
                        ]);

                        Log::info('Wallet balance restored due to order failure', [
                            'wallet_id' => $wallet->id,
                            'user_id' => $order->user_id,
                            'old_balance' => $oldBalance,
                            'refund_amount' => $order->total,
                            'new_balance' => $wallet->fresh()->balance,
                            'order_id' => $orderId,
                            'reason' => $reason
                        ]);
                    }
                }

                // Update transaction status
                $order->transactions()->update(['status' => 'failed']);

                DB::commit();

                Log::info('Order failure handled successfully', [
                    'order_id' => $orderId,
                    'order_number' => $order->order_number,
                    'reason' => $reason
                ]);

                return true;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to handle order failure', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Exception in handleOrderFailure', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cancel an order and restore stock
     */
    public function cancelOrder($orderId, $reason = 'Order cancelled')
    {
        try {
            $order = Order::find($orderId);
            if (!$order) {
                return $this->errorResponse(__('messages.not_found'), [], 404);
            }

            if ($order->payment_status === 'completed') {
                return $this->errorResponse('Cannot cancel completed order', [], 400);
            }

            if ($order->status === 'cancelled') {
                return $this->errorResponse('Order already cancelled', [], 400);
            }

            $result = $this->handleOrderFailure($orderId, $reason);

            if ($result) {
                return $this->successResponse('Order cancelled successfully', [
                    'order_number' => $order->order_number,
                    'status' => 'cancelled'
                ]);
            } else {
                return $this->errorResponse('Failed to cancel order', [], 500);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to cancel order: ' . $e->getMessage(), [], 500);
        }
    }


    public function calculateShippingCost(?float $userLatitude, ?float $userLongitude): float
    {
        $setting = Settings::current();
        if (!$userLatitude || !$userLongitude) {
            // Return fixed shipping cost if no user location provided
            return 00.00; // Default shipping cost
        }

        $distance = $this->calculateDistance($userLatitude, $userLongitude, $setting);
        return $distance * $setting->kilo_shipping_price;
    }

    /**
     * Calculate distance between two points using optimized formula
     * Returns distance in kilometers
     */
    private function calculateDistance(?float $userLatitude, ?float $userLongitude, $setting): float
    {
        if (!$userLatitude || !$userLongitude) {
            return 0;
        }

        // Convert degrees to radians
        $lat1 = deg2rad($setting->latitude);
        $lon1 = deg2rad($setting->longitude);
        $lat2 = deg2rad($userLatitude);
        $lon2 = deg2rad($userLongitude);

        // Earth's radius in kilometers
        $earthRadius = 6371;

        // Calculate differences
        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        // Haversine formula
        $a = sin($dLat / 2) * sin($dLat / 2) + cos($lat1) * cos($lat2) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

}
