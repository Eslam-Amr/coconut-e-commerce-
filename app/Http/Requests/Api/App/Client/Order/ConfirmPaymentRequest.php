<?php

namespace App\Http\Requests\Api\App\Client\Order;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\FlashSale;
use App\Models\OrderItem;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Models\Wallet;
use Illuminate\Validation\ValidationException;

    

class ConfirmPaymentRequest extends MasterRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:cash,wallet,payment_gateway',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
            // 'longitude' => 'nullable|numeric|between:-180,180',
            // 'latitude' => 'nullable|numeric|between:-90,90',
            'address_id' => 'nullable|exists:addresses,id',
            'currency' => 'nullable|string|in:USD,EUR,GBP'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateCart($validator);
            $this->validateVoucher($validator);
            $this->validatePaymentMethod($validator);
            $this->validateAddress($validator);
        });
    }

    /**
     * Validate cart items for order confirmation
     */
    private function validateCart($validator)
    {
        $user = $this->user();
        $cart = Cart::where('user_id', $user->id)->with(['items.product', 'items.productVariant', 'items.flashSale'])->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            $validator->errors()->add('cart', 'Cart is empty');
            return;
        }

        $errors = [];
        $now = now();
// dd($cart);
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
                if ($flashSale->max_limit) {
                    $totalOrdered = OrderItem::whereHas('order', function($q) use ($flashSale) {
                        $q->where('created_at', '>=', $flashSale->start_date)
                          ->where('created_at', '<=', $flashSale->end_date);
                    })
                    ->where('product_id', $item->product_id)
                    ->where('flash_sale_id', $flashSale->id)
                    ->sum('quantity');

                    if (($totalOrdered + $item->quantity) > $flashSale->max_limit) {
                        $available = $flashSale->max_limit - $totalOrdered;
                        $errors[] = "Flash sale limit exceeded for '{$item->product->name}'. Available: {$available}";
                    }
                }
            }

            // Check stock availability
            if ($item->product_variant_id) {
                // Check variant stock from product_variants table
                $variant = ProductVariant::find($item->product_variant_id);

                if (!$variant) {
                    $errors[] = "Product variant not found for '{$item->product->name}'";
                } elseif ($variant->stock < $item->quantity) {
                    $errors[] = "Insufficient stock for variant of '{$item->product->name}'. Available: {$variant->stock}, Requested: {$item->quantity}";
                }
            } else {
                // Check regular product stock from products table
                $product = Product::find($item->product_id);

                if (!$product) {
                    $errors[] = "Product not found for '{$item->product->name}'";
                } elseif ($product->total_quantity < $item->quantity) {
                    $errors[] = "Insufficient stock for '{$item->product->name}'. Available: {$product->total_quantity}, Requested: {$item->quantity}";
                }
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $validator->errors()->add('cart', $error);
            }
        }
    }

    /**
     * Validate voucher code
     */
    private function validateVoucher($validator)
    {
        $voucherCode = $this->input('voucher_code');
        if (!$voucherCode) {
            return;
        }

        $user = $this->user();
        $cart = Cart::where('user_id', $user->id)->with(['items.product', 'items.productVariant', 'items.flashSale'])->first();

        $voucher = Voucher::where('code', $voucherCode)
            ->where('active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$voucher) {
            $validator->errors()->add('voucher_code', 'Invalid or expired voucher code');
            return;
        }

        // Check usage limits
        if ($voucher->usage_limit && $voucher->used_count >= $voucher->usage_limit) {
            $validator->errors()->add('voucher_code', 'Voucher usage limit exceeded');
            return;
        }

        // Check per-user usage limit
        if ($voucher->usage_limit_per_user) {
            $userUsageCount = VoucherUsage::where('voucher_id', $voucher->id)
                ->where('user_id', $user->id)
                ->count();

            if ($userUsageCount >= $voucher->usage_limit_per_user) {
                $validator->errors()->add('voucher_code', 'You have reached the maximum usage limit for this voucher');
                return;
            }
        }
    }

    /**
     * Validate payment method
     */
    private function validatePaymentMethod($validator)
    {
        $paymentMethod = $this->input('payment_method');
        $user = $this->user();
        $cart = Cart::where('user_id', $user->id)->with(['items.product', 'items.productVariant', 'items.flashSale'])->first();

        // Check if cart exists
        if (!$cart) {
            $validator->errors()->add('cart', 'No cart found for this user');
            return;
        }

        // Check if cart has items
        if (!$cart->items || $cart->items->isEmpty()) {
            $validator->errors()->add('cart', 'Cart is empty');
            return;
        }

        // Calculate total amount
        $subtotal = $cart->items->sum(function($item) {
            return $item->price * $item->quantity;
        });

        // Calculate discount if voucher is provided
        $discount = 0;
        if ($this->input('voucher_code')) {
            $voucher = Voucher::where('code', $this->input('voucher_code'))
                ->where('active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
            
            if ($voucher) {
                $discount = min($voucher->discount, $subtotal);
            }
        }

        $total = $subtotal - $discount;

        switch ($paymentMethod) {
            case 'wallet':
                $wallet = Wallet::where('user_id', $user->id)->first();
                if (!$wallet || $wallet->balance < $total) {
                    $validator->errors()->add('payment_method', 'Insufficient wallet balance');
                }
                break;

            case 'payment_gateway':
                // Payment gateway validation - just check if amount is valid
                if ($total <= 0) {
                    $validator->errors()->add('payment_method', 'Invalid payment amount');
                }
                break;

            case 'cash':
                // Cash on delivery is always valid
                break;

            default:
                $validator->errors()->add('payment_method', 'Invalid payment method');
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    

    /**
     * Validate address requirements
     */
    private function validateAddress($validator)
    {
        $user = $this->user();
        $addressId = $this->input('address_id');

        // Check if user has a default address
        $hasDefaultAddress = $user->default_address_id !== null;

        // If user has no default address, address_id is required
        if (!$hasDefaultAddress && !$addressId) {
            $validator->errors()->add('address_id', 'Address is required when you have no default address');
            return;
        }

        // If address_id is provided, validate it belongs to the user
        if ($addressId) {
            $address = \App\Models\Address::where('id', $addressId)
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                $validator->errors()->add('address_id', 'Selected address does not belong to you');
            }
        }
    }
}
