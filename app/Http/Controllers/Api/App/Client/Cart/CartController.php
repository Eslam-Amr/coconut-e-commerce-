<?php

namespace App\Http\Controllers\Api\App\Client\Cart;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Client\Cart\CartService;
use App\Services\Utilities\InteractionPointsService;
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
        private CartService $cartService,
        private InteractionPointsService $interactionService
    ) {}

    public function index(Request $request)
    {
        return $this->cartService->getCart($request);
    }

    public function add(Request $request)
    {
        $result = $this->cartService->add($request);
        
        // Record interaction points for adding to cart
        $userId = auth()->user()?->id;
        if ($userId && $request->has('product_id')) {
            $this->interactionService->recordInteraction($userId, $request->product_id, 'view');
        }
        
        return $result;
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
}


