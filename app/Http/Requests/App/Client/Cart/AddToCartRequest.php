<?php

namespace App\Http\Requests\App\Client\Cart;

use App\Http\Requests\Api\MasterRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AddToCartRequest extends MasterRequest
{

    public function rules(): array
    {
        $required = $this->getRequireOrSometimes();

        return [
            'product_id' => [
                $required, 'integer',
                Rule::exists('products', 'id')->where(fn($q) => $q->where('active', true)),
            ],
            'product_variant_id' => [
                'nullable', 'integer',
                Rule::exists('product_variants', 'id')
                    ->where(fn($q) => $q
                        ->when($this->input('product_id'), fn($qq) => $qq->where('product_id', $this->input('product_id')))
                        ->where('active', true)
                    ),
            ],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $productId = (int) $this->input('product_id');
            if (!$productId) {
                return;
            }

            $user = $this->user();
            if (!$user) {
                return;
            }

            $product = Product::query()->where('id', $productId)->where('active', true)->first();
            if (!$product) {
                return;
            }

            $variantId = $this->input('product_variant_id');
            $variant = null;
            if ($variantId) {
                $variant = ProductVariant::query()->where('id', $variantId)->where('active', true)->first();
                if (!$variant || (int)$variant->product_id !== (int)$product->id) {
                    $validator->errors()->add('product_variant_id', __('messages.variant_mismatch'));
                    return;
                }
            }

            $quantityToAdd = (int)($this->input('quantity') ?? 1);

            // Active flash sale for product or category (highest discount)
            $now = Carbon::parse(now())->toDateTimeString();
            $flashSale = FlashSale::query()
                ->where('active', 1)
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->where(function ($q) use ($product) {
                    $q->where(function ($q2) use ($product) {
                        $q2->where('flashable_type', Product::class)
                            ->where('flashable_id', $product->id);
                    });
                    if (!is_null($product->category_id)) {
                        $q->orWhere(function ($q3) use ($product) {
                            $q3->where('flashable_type', Category::class)
                                ->where('flashable_id', $product->category_id);
                        });
                    }
                })
                ->orderByDesc('discount')
                ->first();

            // Existing cart (do NOT create here; avoid extra insert/select on request validation)
            $cart = Cart::query()->where('user_id', $user->id)->first();

            $existingItem = null;
            if ($cart) {
                $existingItem = CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->where('product_id', $product->id)
                    ->when($variantId, fn($q) => $q->where('product_variant_id', $variantId))
                    ->when(!$variantId, fn($q) => $q->whereNull('product_variant_id'))
                    ->when($flashSale, fn($q) => $q->where('flash_sale_id', $flashSale->id))
                    ->when(!$flashSale, fn($q) => $q->whereNull('flash_sale_id'))
                    ->first();
            }

            $currentQty = $existingItem ? (int)$existingItem->quantity : 0;
            $requestedTotal = $currentQty + $quantityToAdd;

            // Available stock check
            $availableStock = $variant ? (int)$variant->stock : (int)$product->total_quantity;
            if ($requestedTotal > $availableStock) {
                $validator->errors()->add('quantity', __('messages.insufficient_stock'));
                return;
            }

            // Flash sale max limit per cart/user
            if ($flashSale && !empty($flashSale->max_limit) && is_numeric($flashSale->max_limit)) {
                $maxLimit = (int) $flashSale->max_limit;
                if ($requestedTotal > $maxLimit) {
                    $validator->errors()->add('quantity', __('messages.flash_sale_limit_exceeded'));
                }
            }
        });
    }
}


