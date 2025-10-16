<?php

namespace App\Http\Controllers\Api\App\Client\Recommendation;

use App\Http\Controllers\Controller;
use App\Services\Utilities\InteractionPointRecommendationService;
use App\Services\Utilities\InteractionPointsService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;

class InteractionRecommendationController extends Controller implements HasMiddleware
{
    use ApiResponseTrait;

    public static function middleware(): array
    {
        return [
            'client'
        ];
    }

    public function __construct(
        private InteractionPointRecommendationService $recommendationService,
        private InteractionPointsService $interactionService
    ) {}

    /**
     * Get personalized recommendations based on interaction points
     */
    public function getRecommendations(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;
            $recommendations = $this->recommendationService->getRecommendations(Auth::id(), $limit);
            return $this->successResponse($recommendations, 'Personalized recommendations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve recommendations', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get collaborative recommendations based on similar users
     */
    public function getCollaborativeRecommendations(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $recommendations = $this->recommendationService->getCollaborativeRecommendations($user->id, $limit);

            return $this->successResponse($recommendations, 'Collaborative recommendations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve collaborative recommendations', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get trending products based on recent interactions
     */
    public function getTrendingProducts(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $days = (int)($request->query('days', 7));
            $days = $days > 0 ? $days : 7;

            $trending = $this->recommendationService->getTrendingProducts($limit, $days);

            return $this->successResponse($trending, 'Trending products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve trending products', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get related products for a specific product
     */
    public function getRelatedProducts(Request $request, $productId)
    {
        try {
            $limit = (int)($request->query('limit', 6));
            $limit = $limit > 0 ? $limit : 6;

            $related = $this->recommendationService->getRelatedProducts($productId, $limit);

            return $this->successResponse($related, 'Related products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve related products', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get personalized recommendations with detailed explanation
     */
    public function getPersonalizedRecommendations(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $result = $this->recommendationService->getPersonalizedRecommendations($user->id, $limit);

            return $this->successResponse($result, 'Personalized recommendations with explanation retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve personalized recommendations', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get category-specific recommendations
     */
    public function getCategoryRecommendations(Request $request, $categoryId)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $recommendations = $this->recommendationService->getCategoryRecommendations($user->id, $categoryId, $limit);

            return $this->successResponse($recommendations, 'Category recommendations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve category recommendations', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get user interaction statistics
     */
    public function getUserStats(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $stats = $this->interactionService->getUserInteractionStats($user->id);

            return $this->successResponse($stats, 'User interaction statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve user statistics', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Record a product view interaction
     */
    public function recordView(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => ['required', 'integer', 'exists:products,id']
            ]);

            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $this->interactionService->recordInteraction($user->id, $validated['product_id'], 'view');

            return $this->successResponse([], 'View interaction recorded successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to record view interaction', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get recommendations based on interaction points (simple and fast)
     */
    public function getPointBasedRecommendations(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $recommendations = $this->recommendationService->getPointBasedRecommendations($user->id, $limit);

            return $this->successResponse($recommendations, 'Point-based recommendations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve point-based recommendations', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get top rated products based on interaction ratings
     */
    public function getTopRatedProducts(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $minRating = (int)($request->query('min_rating', 4));
            $minRating = $minRating > 0 && $minRating <= 5 ? $minRating : 4;

            $recommendations = $this->recommendationService->getTopRatedProducts($limit, $minRating);

            return $this->successResponse($recommendations, 'Top rated products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve top rated products', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get most reviewed products based on interaction reviews
     */
    public function getMostReviewedProducts(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $minReviews = (int)($request->query('min_reviews', 5));
            $minReviews = $minReviews > 0 ? $minReviews : 5;

            $recommendations = $this->recommendationService->getMostReviewedProducts($limit, $minReviews);

            return $this->successResponse($recommendations, 'Most reviewed products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve most reviewed products', ['error' => $e->getMessage()]);
        }
    }
}