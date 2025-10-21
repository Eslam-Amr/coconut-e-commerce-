<?php

namespace App\Http\Controllers\Api\App\Guest\Recommendation;

use App\Http\Controllers\Controller;
use App\Services\Utilities\InteractionPointRecommendationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class GuestRecommendationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private InteractionPointRecommendationService $recommendationService
    ) {}

    /**
     * Get guest recommendations (popular/trending products)
     */
    public function getRecommendations(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $recommendations = $this->recommendationService->getRecommendations(null, $limit);

            return $this->successResponse($recommendations,  __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve guest recommendations', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get trending products (guest accessible)
     */
    public function getTrendingProducts(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $days = (int)($request->query('days', 7));
            $days = $days > 0 ? $days : 7;

            $trending = $this->recommendationService->getTrendingProducts($limit, $days);

            return $this->successResponse($trending,  __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve trending products', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get point-based recommendations (guest accessible)
     */
    public function getPointBasedRecommendations(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $recommendations = $this->recommendationService->getPointBasedRecommendations(0, $limit);

            return $this->successResponse($recommendations,  __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve point-based recommendations', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get top rated products (guest accessible)
     */
    public function getTopRatedProducts(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $minRating = (int)($request->query('min_rating', 4));
            $minRating = $minRating > 0 && $minRating <= 5 ? $minRating : 4;

            $recommendations = $this->recommendationService->getTopRatedProducts($limit, $minRating);

            return $this->successResponse($recommendations,  __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve top rated products', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get most reviewed products (guest accessible)
     */
    public function getMostReviewedProducts(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $minReviews = (int)($request->query('min_reviews', 5));
            $minReviews = $minReviews > 0 ? $minReviews : 5;

            $recommendations = $this->recommendationService->getMostReviewedProducts($limit, $minReviews);

            return $this->successResponse($recommendations,  __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve most reviewed products', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get category-specific recommendations (guest accessible)
     */
    public function getCategoryRecommendations(Request $request, $categoryId)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $recommendations = $this->recommendationService->getCategoryRecommendations(0, $categoryId, $limit);

            return $this->successResponse($recommendations,  __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve category recommendations', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get related products (guest accessible)
     */
    public function getRelatedProducts(Request $request, $productId)
    {
        try {
            $limit = (int)($request->query('limit', 6));
            $limit = $limit > 0 ? $limit : 6;

            $related = $this->recommendationService->getRelatedProducts($productId, $limit);

            return $this->successResponse($related,  __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve related products', ['error' => $e->getMessage()]);
        }
    }
}
