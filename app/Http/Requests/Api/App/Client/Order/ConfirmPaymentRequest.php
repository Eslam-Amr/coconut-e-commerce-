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
use Illuminate\Support\Facades\Log;
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
            // Keep request validation lightweight and IO-cheap.
            // Heavy validations (stock/limits/wallet amount) are done under DB locks in the service layer.
            $this->validateAddress($validator);
            $this->validateVoucherExists($validator);
            // $this->validateCartExistsAndHasItems($validator);
            $this->validatePaymentMethodBasic($validator);
        });
    }

    /**
     * Validate cart items for order confirmation
     */
    // private function validateCartExistsAndHasItems($validator)
    // {
    //     $user = $this->user();
    //     $cart = Cart::where('user_id', $user->id)->withCount('items')->first();
        
    //     if (!$cart || $cart->items_count === 0) {
    //         $validator->errors()->add('cart', 'Cart is empty');
    //         return;
    //     }
    // }

    /**
     * Validate voucher code
     */
    private function validateVoucherExists($validator)
    {
        $voucherCode = $this->input('voucher_code');
        if (!$voucherCode) {
            return;
        }

        $user = $this->user();
        $cart = Cart::where('user_id', $user->id)->withCount('items')->first();

        $voucher = Voucher::where('code', $voucherCode)
            ->where('active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$voucher) {
            $validator->errors()->add('voucher_code', 'Invalid or expired voucher code');
            return;
        }
    }

    /**
     * Validate payment method
     */
    private function validatePaymentMethodBasic($validator)
    {
        $paymentMethod = $this->input('payment_method');
        $user = $this->user();
        $cart = Cart::where('user_id', $user->id)->withCount('items')->first();

        if (!$cart || $cart->items_count === 0) {
            $validator->errors()->add('cart', 'Cart is empty');
            return;
        }

        // Only basic syntactic validation here; balances and totals are validated in service under lock
        if (!in_array($paymentMethod, ['cash', 'wallet', 'payment_gateway'])) {
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
