<?php

namespace App\Services\Utilities;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Get recommended products for a user or guest.
     */
    public function getRecommendations(?int $userId = null, int $limit = 12): Collection
    {
        if ($userId !== null) {
            return $this->getUserRecommendations($userId, $limit);
        }

        return $this->getGuestRecommendations($limit);
    }

    /**
     * Build recommendations using the user's behavior (orders + wishlist).
     */
    private function getUserRecommendations(int $userId, int $limit): Collection
    {
        $preferredCategoryIds = $this->getUserPreferredCategoryIds($userId, 10);
        $preferredBrandIds = $this->getUserPreferredBrandIds($userId, 10);

        // Exclude products the user already purchased or has in wishlist
        $purchasedProductIds = $this->getUserPurchasedProductIds($userId);
        $wishlistedProductIds = $this->getUserWishlistedProductIds($userId);
        $excludeIds = $purchasedProductIds->merge($wishlistedProductIds)->unique()->values();

        $query = Product::query()
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->when($preferredCategoryIds->isNotEmpty() || $preferredBrandIds->isNotEmpty(), function ($q) use ($preferredCategoryIds, $preferredBrandIds) {
                $q->where(function ($query) use ($preferredCategoryIds, $preferredBrandIds) {
                    if ($preferredCategoryIds->isNotEmpty()) {
                        $query->whereIn('category_id', $preferredCategoryIds);
                    }
                    if ($preferredBrandIds->isNotEmpty()) {
                        $query->orWhereIn('brand_id', $preferredBrandIds);
                    }
                });
            })
            ->when($excludeIds->isNotEmpty(), function ($q) use ($excludeIds) {
                $q->whereNotIn('id', $excludeIds);
            })
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('order_items_count')
            ->orderByDesc('wishlists_count')
            ->orderByDesc('reviews_count')
            ->limit($limit);

        $products = $query->get();

        // If user has very little history, inject diversity and backfill
        if ($products->count() < $limit) {
            $alreadyIds = $excludeIds->merge($products->pluck('id'))->unique();

            // 1) Users also bought (co-purchase) diversity
            $needed = $limit - $products->count();
            $alsoBought = $this->getUsersAlsoBought($userId, $needed, $alreadyIds);
            $products = $products->merge($alsoBought);

            // 2) Cross-category trending diversity
            if ($products->count() < $limit) {
                $needed = $limit - $products->count();
                $crossTrending = $this->getTrendingFromOtherCategories($preferredCategoryIds, $needed, $alreadyIds->merge($products->pluck('id')));
                $products = $products->merge($crossTrending);
            }

            // 3) General popular fallback
            if ($products->count() < $limit) {
                $needed = $limit - $products->count();
                $fallback = $this->getGuestRecommendations($needed, $alreadyIds->merge($products->pluck('id')));
                $products = $products->merge($fallback)->take($limit);
            }
        }

        return $products->values();
    }

    /**
     * Popular products for guests based on sales, wishlists, and reviews.
     */
    private function getGuestRecommendations(int $limit, ?Collection $excludeIds = null): Collection
    {
        $query = Product::query()
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('order_items_count')
            ->orderByDesc('wishlists_count')
            ->orderByDesc('reviews_count')
            ->limit($limit);

        if ($excludeIds && $excludeIds->isNotEmpty()) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->get()->values();
    }

    private function getUserPreferredCategoryIds(int $userId, int $limit = 10): Collection
    {
        // From orders (recent window)
        $fromOrders = OrderItem::query()
            ->select('products.category_id', DB::raw('COUNT(*) as cnt'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.user_id', $userId)
            ->where('orders.created_at', '>=', now()->subMonths(6))
            ->whereNotNull('products.category_id')
            ->groupBy('products.category_id')
            ->orderByDesc('cnt')
            ->limit($limit)
            ->pluck('products.category_id');

        // From wishlist
        $fromWishlist = Wishlist::query()
            ->select('products.category_id', DB::raw('COUNT(*) as cnt'))
            ->join('products', 'products.id', '=', 'wishlists.product_id')
            ->where('wishlists.user_id', $userId)
            ->whereNotNull('products.category_id')
            ->groupBy('products.category_id')
            ->orderByDesc('cnt')
            ->limit($limit)
            ->pluck('products.category_id');

        return $fromOrders->merge($fromWishlist)->filter()->unique()->take($limit)->values();
    }

    private function getUserPreferredBrandIds(int $userId, int $limit = 10): Collection
    {
        // From orders (recent window)
        $fromOrders = OrderItem::query()
            ->select('products.brand_id', DB::raw('COUNT(*) as cnt'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.user_id', $userId)
            ->where('orders.created_at', '>=', now()->subMonths(6))
            ->whereNotNull('products.brand_id')
            ->groupBy('products.brand_id')
            ->orderByDesc('cnt')
            ->limit($limit)
            ->pluck('products.brand_id');

        // From wishlist
        $fromWishlist = Wishlist::query()
            ->select('products.brand_id', DB::raw('COUNT(*) as cnt'))
            ->join('products', 'products.id', '=', 'wishlists.product_id')
            ->where('wishlists.user_id', $userId)
            ->whereNotNull('products.brand_id')
            ->groupBy('products.brand_id')
            ->orderByDesc('cnt')
            ->limit($limit)
            ->pluck('products.brand_id');

        return $fromOrders->merge($fromWishlist)->filter()->unique()->take($limit)->values();
    }

    private function getUserPurchasedProductIds(int $userId): Collection
    {
        return Product::query()
            ->select('products.id')
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('orders.created_at', '>=', now()->subMonths(12))
            ->pluck('products.id')
            ->unique()
            ->values();
    }

    private function getUserWishlistedProductIds(int $userId): Collection
    {
        return Wishlist::query()
            ->where('user_id', $userId)
            ->pluck('product_id')
            ->unique()
            ->values();
    }

    /**
     * Simple co-purchase signal: products bought in the same orders as user's purchases.
     */
    private function getUsersAlsoBought(int $userId, int $limit, Collection $excludeIds): Collection
    {
        $userOrderIds = Order::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subMonths(12))
            ->pluck('id');

        if ($userOrderIds->isEmpty()) {
            return collect();
        }

        $productIds = OrderItem::query()
            ->select('order_items.product_id', DB::raw('COUNT(*) as cnt'))
            ->whereIn('order_items.order_id', $userOrderIds)
            ->groupBy('order_items.product_id')
            ->orderByDesc('cnt')
            ->pluck('order_items.product_id');

        if ($productIds->isEmpty()) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', $productIds)
            ->whereNotIn('id', $excludeIds)
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending products from categories not in the user's preferred list.
     */
    private function getTrendingFromOtherCategories(Collection $preferredCategoryIds, int $limit, Collection $excludeIds): Collection
    {
        $query = Product::query()
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->when($preferredCategoryIds->isNotEmpty(), function ($q) use ($preferredCategoryIds) {
                $q->whereNotIn('category_id', $preferredCategoryIds);
            })
            ->whereNotIn('id', $excludeIds)
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('order_items_count')
            ->orderByDesc('wishlists_count')
            ->orderByDesc('reviews_count')
            ->limit($limit);

        return $query->get();
    }

}


