<?php

namespace App\Http\Requests\App\Client\Cart;

use App\Http\Requests\Api\MasterRequest;
use App\Models\Cart;
use App\Models\CartItem;

class RemoveCartItemRequest extends MasterRequest
{
   
    public function rules(): array
    {
        return [
            'cart_item_id' => ['required', 'integer', 'exists:cart_items,id'],
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
        });
    }
}


