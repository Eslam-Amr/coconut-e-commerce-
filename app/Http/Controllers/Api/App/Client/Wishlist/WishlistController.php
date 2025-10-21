<?php

namespace App\Http\Controllers\Api\App\Client\Wishlist;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Api\App\Client\Wishlist\WishlistService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class WishlistController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            'client'
        ];
    }


    public function __construct(
        private WishlistService $wishlistService
    ) {}

    public function index(Request $request)
    {
        return $this->wishlistService->index($request);
    }

    public function toggleWishlist($productId)
    {
        return $this->wishlistService->toggleWishlist($productId);
    }


}
