<?php

namespace App\Services\Utilities;

use App\Models\Product;
use App\Models\UserProductInteraction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OptimizedRecommendationService
{
    /**
     * Get optimized recommendations - collaborative first, then fallback
     */
    public function getRecommendations(?int $userId = null, int $limit = 12): Collection
    {
        if ($userId !== null) {
            // Try collaborative first
            $collaborative = $this->getCollaborativeRecommendations($userId, $limit);
            if ($collaborative->isNotEmpty()) {
                return $collaborative;
            }
        }

        // Fallback to general recommendations
        return $this->getGeneralRecommendations($limit);
    }

    /**
     * Fast collaborative recommendations using single optimized query
     */
    private function getCollaborativeRecommendations(int $userId, int $limit): Collection
    {
        // Single query to get products with user interaction data and scoring
        return Product::query()
            ->select([
                'products.*',
                // 'products.name',
                // 'products.description',
                // 'products.base_price',
                // 'products.total_quantity',
                DB::raw('COALESCE(upi.total_points, 0) as user_points'),
                DB::raw('COALESCE(avg_rating.avg_rating, 0) as avg_rating'),
                DB::raw('COALESCE(order_count.order_count, 0) as order_count'),
                DB::raw('COALESCE(wishlist_count.wishlist_count, 0) as wishlist_count'),
                DB::raw('COALESCE(review_count.review_count, 0) as review_count')
            ])
            ->leftJoin('user_product_interactions as upi', function ($join) use ($userId) {
                $join->on('products.id', '=', 'upi.product_id')
                     ->where('upi.user_id', '=', $userId);
            })
            ->leftJoin(DB::raw('(SELECT product_id, AVG(rating) as avg_rating FROM product_reviews GROUP BY product_id) as avg_rating'), 'products.id', '=', 'avg_rating.product_id')
            ->leftJoin(DB::raw('(SELECT product_id, COUNT(*) as order_count FROM order_items GROUP BY product_id) as order_count'), 'products.id', '=', 'order_count.product_id')
            ->leftJoin(DB::raw('(SELECT product_id, COUNT(*) as wishlist_count FROM wishlists GROUP BY product_id) as wishlist_count'), 'products.id', '=', 'wishlist_count.product_id')
            ->leftJoin(DB::raw('(SELECT product_id, COUNT(*) as review_count FROM product_reviews GROUP BY product_id) as review_count'), 'products.id', '=', 'review_count.product_id')
            ->where('products.active', true)
            ->where('products.total_quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('upi.total_points')
                      ->orWhere('upi.total_points', '<', 10); // Exclude high-interest products
            })
            ->orderByRaw('
                (COALESCE(avg_rating.avg_rating, 0) * 2 + 
                 COALESCE(order_count.order_count, 0) * 3 + 
                 COALESCE(wishlist_count.wishlist_count, 0) * 2 + 
                 COALESCE(review_count.review_count, 0) * 1) DESC
            ')
            ->limit($limit)
            // ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->with([ 'translations', 'media'])
            ->get();
    }

    /**
     * Fast general recommendations for guests or fallback
     */
    private function getGeneralRecommendations(int $limit): Collection
    {
        return Product::query()
            ->select([
                'products.*',
                DB::raw('COALESCE(avg_rating.avg_rating, 0) as avg_rating'),
                DB::raw('COALESCE(order_count.order_count, 0) as order_count'),
                DB::raw('COALESCE(wishlist_count.wishlist_count, 0) as wishlist_count'),
                DB::raw('COALESCE(review_count.review_count, 0) as review_count')
            ])
            ->leftJoin(DB::raw('(SELECT product_id, AVG(rating) as avg_rating FROM product_reviews GROUP BY product_id) as avg_rating'), 'products.id', '=', 'avg_rating.product_id')
            ->leftJoin(DB::raw('(SELECT product_id, COUNT(*) as order_count FROM order_items GROUP BY product_id) as order_count'), 'products.id', '=', 'order_count.product_id')
            ->leftJoin(DB::raw('(SELECT product_id, COUNT(*) as wishlist_count FROM wishlists GROUP BY product_id) as wishlist_count'), 'products.id', '=', 'wishlist_count.product_id')
            ->leftJoin(DB::raw('(SELECT product_id, COUNT(*) as review_count FROM product_reviews GROUP BY product_id) as review_count'), 'products.id', '=', 'review_count.product_id')
            ->where('products.active', true)
            ->where('products.total_quantity', '>', 0)
            ->orderByRaw('
                (COALESCE(avg_rating.avg_rating, 0) * 2 + 
                 COALESCE(order_count.order_count, 0) * 3 + 
                 COALESCE(wishlist_count.wishlist_count, 0) * 2 + 
                 COALESCE(review_count.review_count, 0) * 1) DESC
            ')
            ->limit($limit)
            // ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->with([ 'translations', 'media'])
            ->get();
    }

    /**
     * Get top-rated products
     */
    public function getTopRatedProducts(int $limit = 12, float $minRating = 4.0): Collection
    {
        $products = Product::query()
            ->select([
                'products.*',
                DB::raw('COALESCE(avg_rating.avg_rating, 0) as avg_rating'),
                DB::raw('COALESCE(avg_rating.review_count, 0) as review_count')
            ])
            ->leftJoin(DB::raw('(SELECT product_id, AVG(rating) as avg_rating, COUNT(*) as review_count FROM product_reviews GROUP BY product_id) as avg_rating'), 'products.id', '=', 'avg_rating.product_id')
            ->where('products.active', true)
            ->where('products.total_quantity', '>', 0)
            ->where('avg_rating.avg_rating', '>=', $minRating)
            ->where('avg_rating.review_count', '>=', 3) // Minimum 3 reviews
            ->orderByDesc('avg_rating.avg_rating')
            ->orderByDesc('avg_rating.review_count')
            ->limit($limit)
            // ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->with(['translations', 'media'])
            ->get();
      // 🔁 Fallback: if fewer than $limit results, fill with random products
      if ($products->count() < $limit) {
        $remaining = $limit - $products->count();

        $fallbacks = Product::query()
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->whereNotIn('id', $products->pluck('id'))
            ->inRandomOrder()
            ->limit($remaining)
            ->with(['translations', 'media'])
            ->get();

        $products = $products->merge($fallbacks);
    }

    return $products;
}


    /**
     * Get most-ordered products
     */
    public function getMostOrderedProducts(int $limit = 12): Collection
    {
        $products =  Product::query()
            ->select([
                'products.*',
                DB::raw('COALESCE(order_count.order_count, 0) as order_count'),
                DB::raw('COALESCE(avg_rating.avg_rating, 0) as avg_rating')
            ])
            ->leftJoin(DB::raw('(SELECT product_id, COUNT(*) as order_count FROM order_items GROUP BY product_id) as order_count'), 'products.id', '=', 'order_count.product_id')
            ->leftJoin(DB::raw('(SELECT product_id, AVG(rating) as avg_rating FROM product_reviews GROUP BY product_id) as avg_rating'), 'products.id', '=', 'avg_rating.product_id')
            ->where('products.active', true)
            ->where('products.total_quantity', '>', 0)
            ->where('order_count.order_count', '>', 0)
            ->orderByDesc('order_count.order_count')
            ->orderByDesc('avg_rating.avg_rating')
            ->limit($limit)
            // ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->with(['translations', 'media'])
            ->get();
            if ($products->count() < $limit) {
                $remaining = $limit - $products->count();
        
                $fallbacks = Product::query()
                    ->where('active', true)
                    ->where('total_quantity', '>', 0)
                    ->whereNotIn('id', $products->pluck('id'))
                    ->inRandomOrder()
                    ->limit($remaining)
                    ->with(['translations', 'media'])
                    ->get();
        
                $products = $products->merge($fallbacks);
            }
        
            return $products;
    }
}
