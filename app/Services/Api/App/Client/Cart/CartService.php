<?php

namespace App\Services\Api\App\Client\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\FlashSale;
use App\Models\Category;
use App\Services\Utilities\CartCalculationService;
use App\Services\Utilities\InteractionPointsService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    use ApiResponseTrait;

    public function __construct(
        private CartCalculationService $calculationService,
        private InteractionPointsService $interactionService
    ) {}

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

                // Detect active flash sale for this product or its category (pick highest discount)
                $now = now();
                $flashSale = FlashSale::query()
                    ->where('active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->where(function($q) use ($product) {
                        $q->where(function($q2) use ($product) {
                            $q2->where('flashable_type', Product::class)
                               ->where('flashable_id', $product->id);
                        });
                        if (!is_null($product->category_id)) {
                            $q->orWhere(function($q3) use ($product) {
                                $q3->where('flashable_type', Category::class)
                                   ->where('flashable_id', $product->category_id);
                            });
                        }
                    })
                    ->orderByDesc('discount')
                    ->first();

                if ($variantId) {
                    $variant = ProductVariant::query()->where('id', $variantId)->where('active', true)->firstOrFail();
                    $unitPrice = $variant->price ?? $unitPrice;
                }

                // Apply flash sale discount if applicable
                $effectivePrice = $unitPrice;
                if ($flashSale && $flashSale->discount > 0) {
                    $effectivePrice = round(max(0, $unitPrice * (1 - ((float)$flashSale->discount / 100))), 2);
                }

                // Check current cart quantity for this item
                $existingItem = CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->where('product_id', $product->id)
                    ->when($variantId, fn($q) => $q->where('product_variant_id', $variantId))
                    ->when(!$variantId, fn($q) => $q->whereNull('product_variant_id'))
                    ->when($flashSale, fn($q) => $q->where('flash_sale_id', $flashSale->id))
                    ->when(!$flashSale, fn($q) => $q->whereNull('flash_sale_id'))
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

                // Enforce flash sale max limit per cart/user if present
                if ($flashSale && !empty($flashSale->max_limit) && is_numeric($flashSale->max_limit)) {
                    $maxLimit = (int)$flashSale->max_limit;
                    $currentFlashQty = $existingItem ? (int)$existingItem->quantity : 0;
                    if ($currentFlashQty + $quantityToAdd > $maxLimit) {
                        $allowed = max(0, $maxLimit - $currentFlashQty);
                        return $this->errorResponse('Flash sale limit exceeded', [
                            'max_limit' => $maxLimit,
                            'current_in_cart' => $currentFlashQty,
                            'trying_to_add' => $quantityToAdd,
                            'allowed_remaining' => $allowed,
                        ], 422);
                    }
                }

                if ($existingItem) {
                    $existingItem->quantity += $quantityToAdd;
                    $existingItem->price = $effectivePrice;
                    $existingItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'flash_sale_id' => $flashSale?->id,
                        'product_variant_id' => $variantId,
                        'quantity' => $quantityToAdd,
                        'price' => $effectivePrice,
                    ]);
                }

                $this->recalculateCartTotals($cart);

                // Record interaction points for adding to cart
                $this->interactionService->recordInteraction($userId, $validated['product_id'], 'view');

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

    /**
     * Calculate cart total with shipping, VAT, and tax
     * Uses user's default address if no address_id provided
     */
    public function calculateTotal(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $validated = $request->validate([
                'address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            ]);

            $result = $this->calculationService->calculateCartTotalByAddress(
                $user->id,
                $validated['address_id'] ?? null
            );

            return $this->successResponse($result, 'Cart total calculated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to calculate cart total', ['error' => $e->getMessage()]);
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
