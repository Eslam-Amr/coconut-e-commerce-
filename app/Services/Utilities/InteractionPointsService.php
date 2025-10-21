<?php

namespace App\Services\Utilities;

use App\Models\UserProductInteraction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class InteractionPointsService
{
    /**
     * Record any interaction and update points
     */
    public function recordInteraction(
        int $userId,
        int $productId,
        string $interactionType,
        ?int $rating = null
    ): UserProductInteraction {
        $interaction = UserProductInteraction::firstOrNew([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        $pointsToAdd = 0;

        switch ($interactionType) {
            case 'view':
                $interaction->view_count++;
                $pointsToAdd = UserProductInteraction::POINTS['view'];
                break;

            case 'wishlist_add':
                $interaction->wishlist_count = 1;
                $pointsToAdd = UserProductInteraction::POINTS['wishlist_add'];
                break;

            case 'wishlist_remove':
                $interaction->wishlist_count = 0;
                $pointsToAdd = UserProductInteraction::POINTS['wishlist_remove'];
                break;

            case 'purchase':
                $interaction->purchase_count++;
                $pointsToAdd = UserProductInteraction::POINTS['purchase'];
                break;

            case 'review':
                $interaction->review_count = 1;
                $pointsToAdd = UserProductInteraction::POINTS['review'];

                if ($rating !== null) {
                    $interaction->last_rating = $rating;
                    $pointsToAdd += UserProductInteraction::POINTS['rating_bonus'][$rating] ?? 0;
                }
                break;

            case 'review_remove':
                $interaction->review_count = 0;
                $pointsToAdd = -UserProductInteraction::POINTS['review'];

                if ($rating !== null) {
                    $pointsToAdd -= UserProductInteraction::POINTS['rating_bonus'][$rating] ?? 0;
                }
                break;

            case 'review_adjustment':
                // For rating adjustments, just add the point difference
                $pointsToAdd = $rating; // rating parameter contains the point difference
                break;
        }

        $interaction->total_points += $pointsToAdd;
        $interaction->last_interaction_at = now();
        $interaction->save();

        return $interaction;
    }

    /**
     * Get user's top interactions based on points
     */
    public function getUserTopInteractions(int $userId, int $limit = 20): Collection
    {
        return UserProductInteraction::forUser($userId)
            // ->withPositivePoints()
            ->where('total_points', '>', 0)


            ->orderByPoints()
            ->orderByLastInteraction()
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's preferred categories based on points
     */
    public function getUserPreferredCategories(int $userId, int $limit = 10): Collection
    {
        return UserProductInteraction::select('products.category_id', DB::raw('SUM(total_points) as total_points'))
            ->join('products', 'products.id', '=', 'user_product_interactions.product_id')
            ->forUser($userId)
            // ->withPositivePoints()
            ->where('total_points', '>', 0)

            ->whereNotNull('products.category_id')
            ->groupBy('products.category_id')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->pluck('products.category_id');
    }

    /**
     * Get user's preferred brands based on points
     */
    public function getUserPreferredBrands(int $userId, int $limit = 10): Collection
    {
        return UserProductInteraction::select('products.brand_id', DB::raw('SUM(total_points) as total_points'))
            ->join('products', 'products.id', '=', 'user_product_interactions.product_id')
            ->forUser($userId)
            // ->withPositivePoints()
            ->where('total_points', '>', 0)

            ->whereNotNull('products.brand_id')
            ->groupBy('products.brand_id')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->pluck('products.brand_id');
    }

    /**
     * Get user's preferred categories and brands in a single query (UNION ALL)
     * Returns an array with keys: 'category_ids' and 'brand_ids'
     */
    public function getUserPreferredCategoriesAndBrands(int $userId, int $limitPerType = 10): array
    {
        $categoriesQuery = UserProductInteraction::select(
                DB::raw('products.category_id as id'),
                DB::raw('SUM(total_points) as points'),
                DB::raw("'category' as type")
            )
            ->join('products', 'products.id', '=', 'user_product_interactions.product_id')
            ->forUser($userId)
            // ->withPositivePoints()
            ->where('total_points', '>', 0)
            ->whereNotNull('products.category_id')
            ->groupBy('products.category_id');

        $brandsQuery = UserProductInteraction::select(
                DB::raw('products.brand_id as id'),
                DB::raw('SUM(total_points) as points'),
                DB::raw("'brand' as type")
            )
            ->join('products', 'products.id', '=', 'user_product_interactions.product_id')
            ->forUser($userId)
            // ->withPositivePoints()
            ->where('total_points', '>', 0)
            ->whereNotNull('products.brand_id')
            ->groupBy('products.brand_id');

        $union = $categoriesQuery->unionAll($brandsQuery);

        // Wrap union to allow ordering and limiting; fetch enough rows for both types
        $rows = DB::query()
            ->fromSub($union, 'prefs')
            ->orderByDesc('points')
            ->limit($limitPerType * 2)
            ->get();

        $categoryIds = $rows->where('type', 'category')->pluck('id')->take($limitPerType)->values();
        $brandIds = $rows->where('type', 'brand')->pluck('id')->take($limitPerType)->values();

        return [
            'category_ids' => $categoryIds,
            'brand_ids' => $brandIds,
        ];
    }

    /**
     * Get category affinity scores (percentage of interest per category)
     */
    public function getCategoryAffinityScores(int $userId): array
    {
        $interactions = UserProductInteraction::select(
            'products.category_id',
            DB::raw('SUM(total_points) as category_points')
        )
            ->join('products', 'products.id', '=', 'user_product_interactions.product_id')
            ->forUser($userId)
            // ->withPositivePoints()
            ->where('total_points', '>', 0)

            ->whereNotNull('products.category_id')
            ->groupBy('products.category_id')
            ->get();

        $totalPoints = $interactions->sum('category_points');

        return $interactions->mapWithKeys(function ($item) use ($totalPoints) {
            return [
                $item->category_id => $totalPoints > 0
                    ? ($item->category_points / $totalPoints)
                    : 0
            ];
        })->toArray();
    }

    /**
     * Get brand affinity scores (percentage of interest per brand)
     */
    public function getBrandAffinityScores(int $userId): array
    {
        $interactions = UserProductInteraction::select(
            'products.brand_id',
            DB::raw('SUM(total_points) as brand_points')
        )
            ->join('products', 'products.id', '=', 'user_product_interactions.product_id')
            ->forUser($userId)
            // ->withPositivePoints()
            ->where('total_points', '>', 0)

            ->whereNotNull('products.brand_id')
            ->groupBy('products.brand_id')
            ->get();

        $totalPoints = $interactions->sum('brand_points');

        return $interactions->mapWithKeys(function ($item) use ($totalPoints) {
            return [
                $item->brand_id => $totalPoints > 0
                    ? ($item->brand_points / $totalPoints)
                    : 0
            ];
        })->toArray();
    }

    /**
     * Get combined affinity scores for categories and brands in ONE query.
     * Returns: [ 'category_affinity' => [categoryId => ratio], 'brand_affinity' => [brandId => ratio] ]
     */
    public function getCombinedAffinityScores(int $userId): array
    {
        $categoriesQuery = UserProductInteraction::select(
                DB::raw('products.category_id as id'),
                DB::raw('SUM(total_points) as points'),
                DB::raw("'category' as type")
            )
            ->join('products', 'products.id', '=', 'user_product_interactions.product_id')
            ->forUser($userId)
            // ->withPositivePoints()
            ->where('total_points', '>', 0)
            ->whereNotNull('products.category_id')
            ->groupBy('products.category_id');

        $brandsQuery = UserProductInteraction::select(
                DB::raw('products.brand_id as id'),
                DB::raw('SUM(total_points) as points'),
                DB::raw("'brand' as type")
            )
            ->join('products', 'products.id', '=', 'user_product_interactions.product_id')
            ->forUser($userId)
            // ->withPositivePoints()
            ->where('total_points', '>', 0)
            ->whereNotNull('products.brand_id')
            ->groupBy('products.brand_id');

        $union = $categoriesQuery->unionAll($brandsQuery);

        // Use window function to compute totals per type in the SAME query
        $rows = DB::query()
            ->fromSub($union, 'prefs')
            ->selectRaw('id, type, points, SUM(points) OVER (PARTITION BY type) as total_points')
            ->get();

        $categoryAffinity = [];
        $brandAffinity = [];

        foreach ($rows as $row) {
            if ($row->type === 'category' && $row->id !== null && $row->total_points > 0) {
                $categoryAffinity[$row->id] = $row->points / $row->total_points;
            }
            if ($row->type === 'brand' && $row->id !== null && $row->total_points > 0) {
                $brandAffinity[$row->id] = $row->points / $row->total_points;
            }
        }

        return [
            'category_affinity' => $categoryAffinity,
            'brand_affinity' => $brandAffinity,
        ];
    }

    /**
     * Apply time decay - reduce points over time
     */
    public function applyTimeDecay(): int
    {
        // Points older than 6 months decrease by 20%
        $affectedRows = UserProductInteraction::where('last_interaction_at', '<', now()->subMonths(6))
            // ->withPositivePoints()
            ->where('total_points', '>', 0)

            ->update([
                'total_points' => DB::raw('GREATEST(FLOOR(total_points * 0.8), 0)')
            ]);

        return $affectedRows;
    }

    /**
     * Get products user has high interest in (purchased or wishlisted)
     */
    public function getUserHighInterestProducts(int $userId, int $minPoints = 10): Collection
    {
        return UserProductInteraction::forUser($userId)
            ->where('total_points', '>=', $minPoints)
            ->pluck('product_id');
    }

    /**
     * Get products user has viewed but not purchased (potential interest)
     */
    public function getUserPotentialInterestProducts(int $userId, int $minViews = 3): Collection
    {
        return UserProductInteraction::forUser($userId)
            ->where('view_count', '>=', $minViews)
            ->where('purchase_count', 0)
            ->where('wishlist_count', 0)
            ->pluck('product_id');
    }

    /**
     * Get similar users based on interaction patterns
     */
    public function getSimilarUsers(int $userId, int $limit = 10): Collection
    {
        // Find users who have similar product interactions
        return UserProductInteraction::select('user_id', DB::raw('COUNT(*) as common_interactions'))
            ->whereIn('product_id', function ($query) use ($userId) {
                $query->select('product_id')
                    ->from('user_product_interactions')
                    ->where('user_id', $userId)
                    // ->withPositivePoints()
                    ->where('total_points', '>', 0)
                ;
            })
            ->where('user_id', '!=', $userId)
            // ->withPositivePoints()
            ->where('total_points', '>', 0)

            ->groupBy('user_id')
            ->orderByDesc('common_interactions')
            ->limit($limit)
            ->pluck('user_id');
    }

    /**
     * Get interaction statistics for a user
     */
    public function getUserInteractionStats(int $userId): array
    {
        $stats = UserProductInteraction::forUser($userId)
            ->selectRaw('
                COUNT(*) as total_interactions,
                SUM(total_points) as total_points,
                SUM(view_count) as total_views,
                SUM(wishlist_count) as total_wishlisted,
                SUM(purchase_count) as total_purchases,
                SUM(review_count) as total_reviews,
                AVG(last_rating) as avg_rating
            ')
            ->first();

        return [
            'total_interactions' => $stats->total_interactions ?? 0,
            'total_points' => $stats->total_points ?? 0,
            'total_views' => $stats->total_views ?? 0,
            'total_wishlisted' => $stats->total_wishlisted ?? 0,
            'total_purchases' => $stats->total_purchases ?? 0,
            'total_reviews' => $stats->total_reviews ?? 0,
            'avg_rating' => round($stats->avg_rating ?? 0, 2),
        ];
    }

    /**
     * Clean up old interactions with zero points
     */
    public function cleanupZeroPointInteractions(): int
    {
        return UserProductInteraction::where('total_points', '<=', 0)
            ->where('last_interaction_at', '<', now()->subMonths(12))
            ->delete();
    }
}
