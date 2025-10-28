<?php

namespace App\Http\Controllers\Api\App\Client\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Client\Cart\AddToCartRequest;
use App\Http\Requests\App\Client\Cart\CalculateCartTotalRequest;
use App\Http\Requests\App\Client\Cart\DecrementCartItemRequest;
use App\Http\Requests\App\Client\Cart\IncrementCartItemRequest;
use App\Http\Requests\App\Client\Cart\RemoveCartItemRequest;
use App\Http\Requests\App\Client\Cart\UpdateCartItemQuantityRequest;
use App\Services\Api\App\Client\Cart\CartService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class CartController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'client'
        ];
    }

    public function __construct(
        private CartService $cartService
    ) {}

    public function index(Request $request)
    {
        return $this->cartService->getCart($request);
    }

    public function add(AddToCartRequest $request)
    {
        return $this->cartService->add($request->validated());
    }

    public function increment(IncrementCartItemRequest $request)
    {
        return $this->cartService->increment($request->validated());
    }

    public function decrement(DecrementCartItemRequest $request)
    {
        return $this->cartService->decrement($request->validated());
    }

    public function updateQuantity(UpdateCartItemQuantityRequest $request)
    {
        return $this->cartService->updateQuantity($request->validated());
    }

    public function remove(RemoveCartItemRequest $request)
    {
        return $this->cartService->remove($request->validated());
    }

    /**
     * Calculate cart total with shipping, VAT, and tax
     * Uses user's default address if no coordinates provided
     */
    public function calculateTotal(CalculateCartTotalRequest $request)
    {
        return $this->cartService->calculateTotal($request->validated());
    }
}


