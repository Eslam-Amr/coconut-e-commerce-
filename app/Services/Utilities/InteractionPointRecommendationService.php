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
        // Get user's preferred categories and brands based on points
        $preferredCategoryIds = $this->interactionService->getUserPreferredCategories($userId, 10);
        $preferredBrandIds = $this->interactionService->getUserPreferredBrands($userId, 10);

        // Exclude products user has high interest in (purchased or wishlisted)
        $excludeIds = $this->interactionService->getUserHighInterestProducts($userId, 10);

        // Get potential interest products (viewed but not purchased)
        $potentialInterestIds = $this->interactionService->getUserPotentialInterestProducts($userId, 3);

        $query = Product::query()
            ->where('active', true)
            ->where('total_quantity', '>', 0)
            ->with(['brand.translations', 'category.translations', 'translations', 'media'])
            ->withAvg('reviews', 'rating')
            ->withCount(['orderItems', 'wishlists', 'reviews']);

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

        // Exclude products user already has high interest in
        if ($excludeIds->isNotEmpty()) {
            $query->whereNotIn('id', $excludeIds);
        }

        // Boost products user has potential interest in
        $products = $query->get();

        // Apply scoring algorithm
        $scoredProducts = $this->scoreProducts($products, $userId, $preferredCategoryIds, $preferredBrandIds, $potentialInterestIds);

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
        Collection $preferredBrandIds,
        Collection $potentialInterestIds
    ): Collection {
        // Get user's affinity scores
        $categoryAffinity = $this->interactionService->getCategoryAffinityScores($userId);
        $brandAffinity = $this->interactionService->getBrandAffinityScores($userId);

        return $products->map(function ($product) use ($categoryAffinity, $brandAffinity, $potentialInterestIds) {
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

            // Potential interest bonus (viewed but not purchased)
            if ($potentialInterestIds->contains($product->id)) {
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
            ->withPositivePoints()
            ->select('product_id', DB::raw('SUM(total_points) as total_points'))
            ->groupBy('product_id')
            ->orderByDesc('total_points')
            ->limit($limit * 2) // Get more to filter
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
            ->limit($limit * 2)
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
}
