<?php

namespace App\Services\Api\App\Client\Cart;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\FlashSale;
use App\Models\Category;
use App\Services\Utilities\CartCalculationService;
use App\Services\Utilities\InteractionPointsService;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
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
                return $this->successResponse([], __('messages.no_cart_found'));
            }

            $cart->load(['items.product.translations', 'items.product.brand.translations', 'items.product.category.translations', 'items.productVariant']);
            return $this->successResponse($cart, __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function add(array $validated)
    {
        try {
            $userId = Auth::id();
            $quantityToAdd = (int)($validated['quantity'] ?? 1);

            return DB::transaction(function () use ($userId, $validated, $quantityToAdd) {
                $cart = $this->getOrCreateUserCart($userId);
                [$product, $variantId, $unitPrice] = $this->resolveProductVariantAndPrice((int)$validated['product_id'], $validated['product_variant_id'] ?? null);
                $flashSale = $this->getActiveFlashSale($product);
                $effectivePrice = $this->calculateEffectivePrice($unitPrice, $flashSale);
                $existingItem = $this->findCartItemInContext($cart, $product, $variantId, $flashSale);

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

                // eager load user to avoid extra query inside recalculate
                $cart->load('user');
                $this->recalculateCartTotals($cart);

                // Record interaction points for adding to cart
                $this->interactionService->recordInteraction($userId, $validated['product_id'], 'view');
                // $this->loadCartRelations($cart);
                return $this->successResponse($cart, __('messages.added_to_cart'));
            });
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.cart_add_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function increment(array $validated)
    {
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


                if ($variantId) {
                    $variant = ProductVariant::query()->where('id', $variantId)->where('active', true)->firstOrFail();
                    if ($variant->product_id !== $product->id) {
                        return $this->errorResponse(__('messages.variant_mismatch'), [], 400);
                    }
                }


                // Check stock availability
                $availableStock = $this->getAvailableStock($product, $variantId);
                $newQuantity = $item->quantity + $by;

                if ($newQuantity > $availableStock) {
                    return $this->errorResponse(
                        __('messages.insufficient_stock_available'),
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
                // $this->loadCartRelations($cart);
                return $this->successResponse($cart, __('messages.cart_item_incremented'));
            });
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_increment_item'), ['error' => $e->getMessage()]);
        }
    }

    public function decrement(array $validated)
    {
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
                // $this->loadCartRelations($cart);
                return $this->successResponse($cart, __('messages.cart_item_decremented'));
            });
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_decrement_item'), ['error' => $e->getMessage()]);
        }
    }

    public function updateQuantity(array $validated)
    {
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
                            __('messages.insufficient_stock_available'),
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
                // $this->loadCartRelations($cart);
                return $this->successResponse($cart, __('messages.cart_item_quantity_updated'));
            });
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_update_quantity'), ['error' => $e->getMessage()]);
        }
    }

    public function remove(array $validated)
    {
        try {
            return DB::transaction(function () use ($validated) {
                $item = CartItem::query()->findOrFail($validated['cart_item_id']);
                $cart = Cart::query()->findOrFail($item->cart_id);

                $this->authorizeItemBelongsToUser($cart);

                $item->delete();

                $this->recalculateCartTotals($cart);
                $this->loadCartRelations($cart);
                return $this->successResponse($cart, __('messages.cart_item_removed'));
            });
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_remove_item'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Calculate cart total with shipping, VAT, and tax
     * Uses user's default address if no address_id provided
     */
    public function calculateTotal(array $validated)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $result = $this->calculationService->calculateCartTotalByAddress(
                $user->id,
                $validated['address_id'] ?? null
            );

            return $this->successResponse($result, __('messages.cart_total_calculated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_calculate_cart_total'), ['error' => $e->getMessage()]);
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
        
        // Calculate VAT and tax on subtotal
        $vatAmount = $this->calculationService->calculateVAT($subtotal);
        $taxAmount = $this->calculationService->calculateTax($subtotal);
        
        // Calculate shipping cost
        $address = Address::find($cart->user->default_address_id);
        $shippingPrice = (float) ($cart->shipping_price != 0 ? $cart->shipping_price : $this->calculationService->calculateShippingCost($address?->latitude, $address?->longitude));
        
        // Calculate final total: subtotal - discount + VAT + tax + shipping
        $total = max(0, $subtotal - $discount + $vatAmount + $taxAmount + $shippingPrice);
        
        $cart->subtotal = $subtotal;
        $cart->discount = $discount;
        $cart->vat_amount = $vatAmount;
        $cart->tax_amount = $taxAmount;
        $cart->shipping_price = $shippingPrice;
        $cart->total = $total;
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

    private function resolveProductVariantAndPrice(int $productId, ?int $variantId): array
    {
        $product = Product::query()->where('id', $productId)->where('active', true)->firstOrFail();
        $unitPrice = $product->base_price;
        if ($variantId) {
            $variant = ProductVariant::query()->where('id', $variantId)->where('active', true)->firstOrFail();
           
            $unitPrice = $variant->price ?? $unitPrice;
            return [$product, $variantId, $unitPrice];
        }
        return [$product, null, $unitPrice];
    }

    private function getActiveFlashSale(Product $product): ?FlashSale
    {
        $now = Carbon::parse(now())->toDateTimeString();
        // Cache per product+category+time window for one minute to reduce duplicate queries within a request burst
        return FlashSale::query()
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
    }

    private function calculateEffectivePrice(float $unitPrice, ?FlashSale $flashSale): float
    {
        if (!$flashSale || $flashSale->discount <= 0) {
            return $unitPrice;
        }
        return round(max(0, $unitPrice * (1 - ((float)$flashSale->discount / 100))), 2);
    }

    private function findCartItemInContext(Cart $cart, Product $product, ?int $variantId, ?FlashSale $flashSale): ?CartItem
    {
        return CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->when($variantId, fn($q) => $q->where('product_variant_id', $variantId))
            ->when(!$variantId, fn($q) => $q->whereNull('product_variant_id'))
            ->when($flashSale, fn($q) => $q->where('flash_sale_id', $flashSale->id))
            ->when(!$flashSale, fn($q) => $q->whereNull('flash_sale_id'))
            ->first();
    }

    private function loadCartRelations(Cart $cart): void
    {
        $cart->load(['items.product.translations', 'items.product.brand.translations', 'items.product.category.translations', 'items.productVariant']);
    }
}
