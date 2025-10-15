<?php

namespace App\Services\Api\App\Client\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    use ApiResponseTrait;

    public function getCart(Request $request)
    {
        try {
            // $cart = $this->getOrCreateUserCart(Auth::id());

            $cart = Cart::query()->where('user_id', Auth::id())->first();
            if (!$cart) {
                return $this->successResponse([], 'no cart found and no item');
            }

            $cart->load(['items.product.translations', 'items.product.brand.translations', 'items.product.category.translations', 'items.productVariant']);
            return $this->successResponse($cart, 'Cart retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve cart', ['error' => $e->getMessage()]);
        }
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $userId = Auth::id();
            $quantityToAdd = (int)($validated['quantity'] ?? 1);

            return DB::transaction(function () use ($userId, $validated, $quantityToAdd) {
                $cart = $this->getOrCreateUserCart($userId);

                $product = Product::query()->where('id', $validated['product_id'])->where('active', true)->firstOrFail();

                $variantId = $validated['product_variant_id'] ?? null;
                $unitPrice = $product->base_price; // fallback

                if ($variantId) {
                    $variant = ProductVariant::query()->where('id', $variantId)->where('active', true)->firstOrFail();
                    $unitPrice = $variant->price ?? $unitPrice;
                }

                // Check current cart quantity for this item
                $existingItem = CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->where('product_id', $product->id)
                    ->when($variantId, fn($q) => $q->where('product_variant_id', $variantId))
                    ->when(!$variantId, fn($q) => $q->whereNull('product_variant_id'))
                    ->first();

                $currentCartQuantity = $existingItem ? $existingItem->quantity : 0;
                $requestedTotalQuantity = $currentCartQuantity + $quantityToAdd;

                // Validate stock availability
                $availableStock = $this->getAvailableStock($product, $variantId);

                if ($requestedTotalQuantity > $availableStock) {
                    return $this->errorResponse(
                        'Insufficient stock available',
                        [
                            'available_stock' => $availableStock,
                            'requested_quantity' => $requestedTotalQuantity,
                            'current_cart_quantity' => $currentCartQuantity,
                            'trying_to_add' => $quantityToAdd
                        ],
                        422
                    );
                }

                if ($existingItem) {
                    $existingItem->quantity += $quantityToAdd;
                    $existingItem->price = $unitPrice;
                    $existingItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variantId,
                        'quantity' => $quantityToAdd,
                        'price' => $unitPrice,
                    ]);
                }

                $this->recalculateCartTotals($cart);

                $cart->load(['items.product.translations', 'items.product.brand.translations', 'items.product.category.translations', 'items.productVariant']);

                return $this->successResponse($cart, 'Item added to cart successfully');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to add to cart', ['error' => $e->getMessage()]);
        }
    }

    public function increment(Request $request)
    {
        $validated = $request->validate([
            'cart_item_id' => ['required', 'integer', 'exists:cart_items,id'],
            'by' => ['nullable', 'integer', 'min:1']
        ]);

        try {
            $by = (int)($validated['by'] ?? 1);
            return DB::transaction(function () use ($validated, $by) {
                $item = CartItem::query()->findOrFail($validated['cart_item_id']);
                $cart = Cart::query()->findOrFail($item->cart_id);

                $this->authorizeItemBelongsToUser($cart);

                // Load product and variant for stock validation
                $item->load(['product', 'productVariant']);
                $product = $item->product;
                $variantId = $item->product_variant_id;

                // Check stock availability
                $availableStock = $this->getAvailableStock($product, $variantId);
                $newQuantity = $item->quantity + $by;

                if ($newQuantity > $availableStock) {
                    return $this->errorResponse(
                        'Insufficient stock available',
                        [
                            'available_stock' => $availableStock,
                            'current_quantity' => $item->quantity,
                            'trying_to_increment_by' => $by,
                            'requested_total_quantity' => $newQuantity
                        ],
                        422
                    );
                }

                $item->quantity = $newQuantity;
                $item->save();

                $this->recalculateCartTotals($cart);

                $cart->load(['items.product.translations', 'items.product.brand.translations', 'items.product.category.translations', 'items.productVariant']);
                return $this->successResponse($cart, 'Cart item incremented');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to increment item', ['error' => $e->getMessage()]);
        }
    }

    public function decrement(Request $request)
    {
        $validated = $request->validate([
            'cart_item_id' => ['required', 'integer', 'exists:cart_items,id'],
            'by' => ['nullable', 'integer', 'min:1']
        ]);

        try {
            $by = (int)($validated['by'] ?? 1);
            return DB::transaction(function () use ($validated, $by) {
                $item = CartItem::query()->findOrFail($validated['cart_item_id']);
                $cart = Cart::query()->findOrFail($item->cart_id);

                $this->authorizeItemBelongsToUser($cart);

                $newQty = max(0, $item->quantity - $by);
                if ($newQty === 0) {
                    $item->delete();
                } else {
                    $item->quantity = $newQty;
                    // $item->line_total = $item->quantity * $item->price;
                    $item->save();
                }

                $this->recalculateCartTotals($cart);

                $cart->load(['items.product.translations', 'items.product.brand.translations', 'items.product.category.translations', 'items.productVariant']);
                return $this->successResponse($cart, 'Cart item decremented');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to decrement item', ['error' => $e->getMessage()]);
        }
    }

    public function updateQuantity(Request $request)
    {
        $validated = $request->validate([
            'cart_item_id' => ['required', 'integer', 'exists:cart_items,id'],
            'quantity' => ['required', 'integer', 'min:0']
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $item = CartItem::query()->findOrFail($validated['cart_item_id']);
                $cart = Cart::query()->findOrFail($item->cart_id);

                $this->authorizeItemBelongsToUser($cart);

                $newQuantity = (int)$validated['quantity'];

                if ($newQuantity === 0) {
                    $item->delete();
                } else {
                    // Load product and variant for stock validation
                    $item->load(['product', 'productVariant']);
                    $product = $item->product;
                    $variantId = $item->product_variant_id;

                    // Check stock availability
                    $availableStock = $this->getAvailableStock($product, $variantId);

                    if ($newQuantity > $availableStock) {
                        return $this->errorResponse(
                            'Insufficient stock available',
                            [
                                'available_stock' => $availableStock,
                                'requested_quantity' => $newQuantity
                            ],
                            422
                        );
                    }

                    $item->quantity = $newQuantity;
                    $item->save();
                }

                $this->recalculateCartTotals($cart);

                $cart->load(['items.product.translations', 'items.product.brand.translations', 'items.product.category.translations', 'items.productVariant']);
                return $this->successResponse($cart, 'Cart item quantity updated');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update quantity', ['error' => $e->getMessage()]);
        }
    }

    public function remove(Request $request)
    {
        $validated = $request->validate([
            'cart_item_id' => ['required', 'integer', 'exists:cart_items,id']
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $item = CartItem::query()->findOrFail($validated['cart_item_id']);
                $cart = Cart::query()->findOrFail($item->cart_id);

                $this->authorizeItemBelongsToUser($cart);

                $item->delete();

                $this->recalculateCartTotals($cart);

                $cart->load(['items.product.translations', 'items.product.brand.translations', 'items.product.category.translations', 'items.productVariant']);
                return $this->successResponse($cart, 'Cart item removed');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to remove item', ['error' => $e->getMessage()]);
        }
    }

    private function getOrCreateUserCart(int $userId): Cart
    {
        $cart = Cart::query()->firstOrCreate(
            ['user_id' => $userId],
            ['subtotal' => 0, 'discount' => 0, 'total' => 0]
        );
        return $cart;
    }

    private function recalculateCartTotals(Cart $cart): void
    {
        $subtotal = (float) CartItem::query()->where('cart_id', $cart->id)->sum(DB::raw('quantity * price'));
        $discount = (float) ($cart->discount ?? 0);
        $cart->subtotal = $subtotal;
        $cart->total = max(0, $subtotal - $discount);
        $cart->save();
    }

    private function authorizeItemBelongsToUser(Cart $cart): void
    {
        if ($cart->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Get available stock for a product or variant
     */
    private function getAvailableStock(Product $product, ?int $variantId = null): int
    {
        if ($variantId) {
            // If variant is specified, return variant stock
            $variant = ProductVariant::query()
                ->where('id', $variantId)
                ->where('active', true)
                ->first();

            return $variant ? $variant->stock : 0;
        } else {
            // If no variant, return product total quantity
            return $product->total_quantity;
        }
    }
}
