<?php

namespace App\Http\Controllers\Api\App\Client\Wishlist;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Api\App\Client\Wishlist\WishlistService;
use App\Services\Utilities\InteractionPointsService;
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
        private WishlistService $wishlistService,
        private InteractionPointsService $interactionService
    ) {}

    public function index(Request $request)
    {
        return $this->wishlistService->index($request);
    }

    public function toggleWishlist($productId)
    {
        $result = $this->wishlistService->toggleWishlist($productId);
        
        // Record interaction points
        $userId = auth()->user()?->id;
        if ($userId) {
            // Check if item was added or removed based on response
            $responseData = $result->getData();
            if (isset($responseData->data->in_wishlist)) {
                if ($responseData->data->in_wishlist) {
                    $this->interactionService->recordInteraction($userId, $productId, 'wishlist_add');
                } else {
                    $this->interactionService->recordInteraction($userId, $productId, 'wishlist_remove');
                }
            }
        }
        
        return $result;
    }


}
