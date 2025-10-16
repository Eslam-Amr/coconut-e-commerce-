<?php

namespace App\Services\Utilities;

use App\Models\Product;
use App\Models\UserProductInteraction;
use App\Services\Utilities\InteractionPointsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InteractionPointRecommendationService
{
    private InteractionPointsService $interactionService;

    public function __construct(InteractionPointsService $interactionService)
    {
        $this->interactionService = $interactionService;
    }

    /**
     * Get recommendations for a user based on interaction points
     */
    public function getRecommendations(?int $userId = null, int $limit = 12): Collection
    {
        if ($userId !== null) {
            return $this->getUserRecommendations($userId, $limit);
        }

        return $this->getGuestRecommendations($limit);
    }

    /**
     * Get user-specific recommendations based on interaction points
     */
    private function getUserRecommendations(int $userId, int $limit): Collection
    {
        // Get user's preferred categories and brands in one query
        $prefs = $this->interactionService->getUserPreferredCategoriesAndBrands($userId, 10);
        $preferredCategoryIds = collect($prefs['category_ids'] ?? []);
        $preferredBrandIds = collect($prefs['brand_ids'] ?? []);

        // Use left join to bring user's interaction stats, avoid extra queries
        $minHighInterestPoints = 10;

        $query = Product::query()
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->leftJoin('user_product_interactions as upi', function ($j) use ($userId) {
                $j->on('upi.product_id', '=', 'products.id')
                  ->where('upi.user_id', '=', $userId);
            })
            ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            // Cap result size to avoid loading too many rows before scoring
            ->limit( 10);

        // Apply category and brand preferences
        if ($preferredCategoryIds->isNotEmpty() || $preferredBrandIds->isNotEmpty()) {
            $query->where(function ($q) use ($preferredCategoryIds, $preferredBrandIds) {
                if ($preferredCategoryIds->isNotEmpty()) {
                    $q->whereIn('category_id', $preferredCategoryIds);
                }
                if ($preferredBrandIds->isNotEmpty()) {
                    $q->orWhereIn('brand_id', $preferredBrandIds);
                }
            });
        }

        // Exclude high-interest products using the joined interaction
        $query->where(function ($q) use ($minHighInterestPoints) {
            $q->whereNull('upi.total_points')
              ->orWhere('upi.total_points', '<', $minHighInterestPoints);
        });

        // Fetch candidate products (with joined interaction columns)
        $products = $query->select('products.*',
            DB::raw('COALESCE(upi.view_count, 0) as upi_view_count'),
            DB::raw('COALESCE(upi.purchase_count, 0) as upi_purchase_count'),
            DB::raw('COALESCE(upi.wishlist_count, 0) as upi_wishlist_count')
        )->get();

        // Apply scoring algorithm
        $scoredProducts = $this->scoreProducts($products, $userId, $preferredCategoryIds, $preferredBrandIds);

        return $scoredProducts->take($limit)->values();
    }

    /**
     * Get guest recommendations (fallback to trending/popular)
     */
    private function getGuestRecommendations(int $limit): Collection
    {
        return Product::query()
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('order_items_count')
            ->orderByDesc('wishlists_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Score products based on user preferences and interaction patterns
     */
    private function scoreProducts(
        Collection $products, 
        int $userId, 
        Collection $preferredCategoryIds, 
        Collection $preferredBrandIds
    ): Collection {
        // Get user's affinity scores in a single query
        $affinity = $this->interactionService->getCombinedAffinityScores($userId);
        $categoryAffinity = $affinity['category_affinity'] ?? [];
        $brandAffinity = $affinity['brand_affinity'] ?? [];

        return $products->map(function ($product) use ($categoryAffinity, $brandAffinity) {
            $score = 0;

            // Base score from product popularity
            $score += ($product->reviews_avg_rating ?? 0) * 2; // Rating weight
            $score += $product->order_items_count * 3; // Purchase popularity
            $score += $product->wishlists_count * 2; // Wishlist popularity
            $score += $product->reviews_count * 1; // Review activity

            // Category affinity bonus
            if (isset($categoryAffinity[$product->category_id])) {
                $score += $categoryAffinity[$product->category_id] * 50;
            }

            // Brand affinity bonus
            if (isset($brandAffinity[$product->brand_id])) {
                $score += $brandAffinity[$product->brand_id] * 30;
            }

            // Potential interest bonus (viewed but not purchased, no wishlist)
            if ((int)($product->upi_view_count ?? 0) >= 3
                && (int)($product->upi_purchase_count ?? 0) === 0
                && (int)($product->upi_wishlist_count ?? 0) === 0) {
                $score += 25;
            }

            // Freshness bonus (newer products get slight boost)
            $daysSinceCreated = now()->diffInDays($product->created_at);
            if ($daysSinceCreated < 30) {
                $score += 10;
            } elseif ($daysSinceCreated < 90) {
                $score += 5;
            }

            $product->recommendation_score = $score;
            return $product;
        })->sortByDesc('recommendation_score');
    }

    /**
     * Get recommendations based on similar users
     */
    public function getCollaborativeRecommendations(int $userId, int $limit = 12): Collection
    {
        // Find similar users
        $similarUserIds = $this->interactionService->getSimilarUsers($userId, 20);

        if ($similarUserIds->isEmpty()) {
            return $this->getUserRecommendations($userId, $limit);
        }

        // Get products liked by similar users that current user hasn't interacted with
        $userInteractionIds = UserProductInteraction::forUser($userId)
            ->pluck('product_id');

        $recommendedProductIds = UserProductInteraction::whereIn('user_id', $similarUserIds)
            ->whereNotIn('product_id', $userInteractionIds)
            ->where('total_points', '>', 0)
            ->select('product_id', DB::raw('SUM(total_points) as total_points'))
            ->groupBy('product_id')
            ->orderByDesc('total_points')
            ->limit($limit ) 
            ->pluck('product_id');

        return Product::query()
            ->whereIn('id', $recommendedProductIds)
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending products based on recent interactions
     */
    public function getTrendingProducts(int $limit = 12, int $days = 7): Collection
    {
        $recentProductIds = UserProductInteraction::where('last_interaction_at', '>=', now()->subDays($days))
            ->select('product_id', DB::raw('SUM(total_points) as total_points'))
            ->groupBy('product_id')
            ->orderByDesc('total_points')
            ->limit($limit )
            ->pluck('product_id');

        return Product::query()
            ->whereIn('id', $recentProductIds)
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get recommendations for a specific product (related products)
     */
    public function getRelatedProducts(int $productId, int $limit = 6): Collection
    {
        $product = Product::find($productId);
        if (!$product) {
            return collect();
        }

        // Find products in same category with similar brand
        $query = Product::query()
            ->where('id', '!=', $productId)
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews']);

        // Same category gets priority
        $query->where(function ($q) use ($product) {
            $q->where('category_id', $product->category_id)
              ->orWhere('brand_id', $product->brand_id);
        });

        return $query->orderByDesc('reviews_avg_rating')
            ->orderByDesc('order_items_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get personalized recommendations with explanation
     */
    public function getPersonalizedRecommendations(int $userId, int $limit = 12): array
    {
        $recommendations = $this->getUserRecommendations($userId, $limit);
        $userStats = $this->interactionService->getUserInteractionStats($userId);

        return [
            'recommendations' => $recommendations,
            'user_stats' => $userStats,
            'explanation' => $this->generateExplanation($userStats, $recommendations),
        ];
    }

    /**
     * Generate explanation for recommendations
     */
    private function generateExplanation(array $userStats, Collection $recommendations): string
    {
        $explanations = [];

        if ($userStats['total_purchases'] > 0) {
            $explanations[] = "Based on your {$userStats['total_purchases']} previous purchases";
        }

        if ($userStats['total_wishlisted'] > 0) {
            $explanations[] = "and {$userStats['total_wishlisted']} wishlisted items";
        }

        if ($userStats['total_views'] > 0) {
            $explanations[] = "plus your browsing history of {$userStats['total_views']} product views";
        }

        if (empty($explanations)) {
            return "Based on popular and trending products";
        }

        return "Based on " . implode(', ', $explanations);
    }

    /**
     * Get category-specific recommendations
     */
    public function getCategoryRecommendations(int $userId, int $categoryId, int $limit = 12): Collection
    {
        $userCategoryAffinity = $this->interactionService->getCategoryAffinityScores($userId);
        $categoryScore = $userCategoryAffinity[$categoryId] ?? 0;

        $query = Product::query()
            ->where('category_id', $categoryId)
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews']);

        // Exclude products user already has high interest in
        $excludeIds = $this->interactionService->getUserHighInterestProducts($userId, 5);
        if ($excludeIds->isNotEmpty()) {
            $query->whereNotIn('id', $excludeIds);
        }

        $products = $query->get();

        // Score based on user's interest in this category
        $scoredProducts = $products->map(function ($product) use ($categoryScore) {
            $score = 0;
            $score += ($product->reviews_avg_rating ?? 0) * 2;
            $score += $product->order_items_count * 3;
            $score += $product->wishlists_count * 2;
            $score += $categoryScore * 100; // Boost based on user's category affinity

            $product->recommendation_score = $score;
            return $product;
        })->sortByDesc('recommendation_score');

        return $scoredProducts->take($limit)->values();
    }

    /**
     * Get recommendations based on interaction points (simple and fast)
     * Returns products ordered by highest total interaction points
     */
    public function getPointBasedRecommendations(int $userId, int $limit = 12): Collection
    {
        // Single round-trip: join aggregated interaction totals and filter in one query
        return Product::query()
            ->joinSub(
                UserProductInteraction::select('product_id', DB::raw('SUM(total_points) as total_points'))
                    ->groupBy('product_id'),
                'agg',
                'agg.product_id',
                '=',
                'products.id'
            )
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->orderByDesc('agg.total_points')
            ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get top rated products based on interaction ratings
     * Returns products ordered by highest average rating from interactions
     */
    public function getTopRatedProducts(int $limit = 12, int $minRating = 4): Collection
    {
        // Single round-trip: join aggregated avg rating
        $products = Product::query()
            ->joinSub(
                UserProductInteraction::select('product_id', DB::raw('AVG(last_rating) as avg_rating'))
                    ->whereNotNull('last_rating')
                    ->where('last_rating', '>=', $minRating)
                    ->groupBy('product_id')
                    ->having('avg_rating', '>=', $minRating),
                'agg',
                'agg.product_id',
                '=',
                'products.id'
            )
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->orderByDesc('agg.avg_rating')
            ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            ->limit($limit)
            ->get();

        if ($products->isEmpty()) {
            return $this->getGuestRecommendations($limit);
        }

        return $products;
    }

    /**
     * Get most reviewed products based on interaction reviews
     * Returns products ordered by highest review count from interactions
     */
    public function getMostReviewedProducts(int $limit = 12, int $minReviews = 5): Collection
    {
        // Single round-trip: join aggregated review totals
        $products = Product::query()
            ->joinSub(
                UserProductInteraction::select('product_id', DB::raw('SUM(review_count) as total_reviews'))
                    ->where('review_count', '>', 0)
                    ->groupBy('product_id')
                    ->having('total_reviews', '>=', $minReviews),
                'agg',
                'agg.product_id',
                '=',
                'products.id'
            )
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->orderByDesc('agg.total_reviews')
            ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews'])
            ->limit($limit)
            ->get();

        if ($products->isEmpty()) {
            return $this->getGuestRecommendations($limit);
        }

        return $products;
    }
}
