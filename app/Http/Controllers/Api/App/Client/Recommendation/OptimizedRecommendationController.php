<?php

namespace App\Http\Controllers\Api\App\Client\Recommendation;

use App\Http\Controllers\Controller;
use App\Services\Utilities\OptimizedRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;

class OptimizedRecommendationController extends Controller 
{


    public function __construct(private OptimizedRecommendationService $recommendationService) {}

    /**
     * Get recommendations - collaborative first, then fallback to general
     */
    public function getRecommendations(Request $request)
    {
        try {
            // Get user ID (middleware already loaded user)
            $userId = Auth::user()?->id;
            $limit = $request->get('limit', 12);

            $recommendations = $this->recommendationService->getRecommendations($userId, $limit);

            return response()->json([
                'success' => true,
                'message' =>  __('messages.retrieved_successfully'),
                'data' => $recommendations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recommendations',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
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

            $products = $this->recommendationService->getTopRatedProducts($limit, $minRating);

            return response()->json([
                'success' => true,
                'message' =>   __('messages.retrieved_successfully'),
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top-rated products',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Get most-ordered products
     */
    public function getMostOrderedProducts(Request $request)
    {
        try {
            $limit = $request->get('limit', 12);

            $products = $this->recommendationService->getMostOrderedProducts($limit);

            return response()->json([
                'success' => true,
                'message' =>  __('messages.retrieved_successfully'),
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve most-ordered products',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }
}
