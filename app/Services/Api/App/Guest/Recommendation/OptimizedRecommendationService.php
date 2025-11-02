<?php

namespace App\Services\Api\App\Guest\Recommendation;

use App\Services\Utilities\OptimizedRecommendationService as BaseRecommendationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptimizedRecommendationService
{
    use ApiResponseTrait;

    protected $baseRecommendationService;

    public function __construct(BaseRecommendationService $baseRecommendationService)
    {
        $this->baseRecommendationService = $baseRecommendationService;
    }

    /**
     * Get recommendations - collaborative first, then fallback to general
     */
    public function getRecommendations(Request $request)
    {
        try {
            $userId = Auth::id();
            $limit = $request->get('limit', 12);

            $recommendations = $this->baseRecommendationService->getRecommendations($userId, $limit);

            return $this->successResponse($recommendations, __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve recommendations', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get top-rated products
     */
    public function getTopRatedProducts(Request $request)
    {
        try {
            $limit = $request->get('limit', 12);
            $minRating = $request->get('min_rating', 4.0);

            $products = $this->baseRecommendationService->getTopRatedProducts($limit, $minRating);

            return $this->successResponse($products, __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve top-rated products', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get most-ordered products
     */
    public function getMostOrderedProducts(Request $request)
    {
        try {
            $limit = $request->get('limit', 12);

            $products = $this->baseRecommendationService->getMostOrderedProducts($limit);

            return $this->successResponse($products, __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve most-ordered products', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
