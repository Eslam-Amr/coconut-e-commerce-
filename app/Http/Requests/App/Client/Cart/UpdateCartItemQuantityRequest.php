<?php

namespace App\Http\Requests\App\Client\Cart;

use App\Http\Requests\Api\MasterRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;

class UpdateCartItemQuantityRequest extends MasterRequest
{

    public function rules(): array
    {
        return [
            'cart_item_id' => ['required', 'integer', 'exists:cart_items,id'],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $itemId = (int) $this->input('cart_item_id');
            if (!$itemId) {
                return;
            }

            $item = CartItem::query()->find($itemId);
            if (!$item) {
                return;
            }

            $cart = Cart::query()->find($item->cart_id);
            if (!$cart || $cart->user_id !== optional($this->user())->id) {
                $validator->errors()->add('cart_item_id', 'Unauthorized item access.');
                return;
            }

            $newQuantity = (int) $this->input('quantity');
            if ($newQuantity === 0) {
                return;
            }

            $product = Product::query()->find($item->product_id);
            $variantId = $item->product_variant_id;
            $variant = $variantId ? ProductVariant::query()->find($variantId) : null;
            $availableStock = $variant ? (int)$variant->stock : (int)($product->total_quantity ?? 0);

            if ($newQuantity > $availableStock) {
                $validator->errors()->add('quantity', 'Insufficient stock available');
            }
        });
    }
}


