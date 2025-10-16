<?php

namespace App\Http\Controllers\Api\App\Client\Cart;

use App\Http\Controllers\Controller;
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

    public function add(Request $request)
    {
        return $this->cartService->add($request);
    }

    public function increment(Request $request)
    {
        return $this->cartService->increment($request);
    }

    public function decrement(Request $request)
    {
        return $this->cartService->decrement($request);
    }

    public function updateQuantity(Request $request)
    {
        return $this->cartService->updateQuantity($request);
    }

    public function remove(Request $request)
    {
        return $this->cartService->remove($request);
    }

    /**
     * Calculate cart total with shipping, VAT, and tax
     * Uses user's default address if no coordinates provided
     */
    public function calculateTotal(Request $request)
    {
        return $this->cartService->calculateTotal($request);
    }
}


