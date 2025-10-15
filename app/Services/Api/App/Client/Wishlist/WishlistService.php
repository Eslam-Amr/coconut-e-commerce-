<?php

namespace App\Services\Api\App\Client\Wishlist;

use App\Models\Product;
use App\Models\Wishlist;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = (int)($request->query('per_page', 12));
            $perPage = $perPage > 0 ? $perPage : 12;

            $wishlists = Wishlist::query()
                ->where('user_id', Auth::id())
                ->with(['product.translations', 'product.brand.translations', 'product.category.translations'])
                ->paginate($perPage);

            return $this->successResponse($wishlists, 'Wishlist retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve wishlist', ['error' => $e->getMessage()]);
        }
    }
    public function toggleWishlist($productId)
    {
        try {
            $userId = Auth::id();
            $existingWishlist = Wishlist::where('user_id', $userId)->where('product_id', $productId)->first();

            if ($existingWishlist)
                return $this->removeFromWishlist($existingWishlist);


            return $this->addToWishlist($userId, $productId);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to add to wishlist', ['error' => $e->getMessage()]);
        }
    }
    /**
     * Remove product from wishlist.
     */
    private function removeFromWishlist($wishlist)
    {
        $wishlist->delete();

        return $this->successResponse([
            'message' => 'Product removed from wishlist successfully.',
            'in_wishlist' => false
        ]);
    }

    /**
     * Add product to wishlist.
     */
    private function addToWishlist($userId, $productId)
    {
        Wishlist::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return $this->successResponse([
            'message' => 'Product added to wishlist successfully.',
            'in_wishlist' => true
        ]);
    }
}
