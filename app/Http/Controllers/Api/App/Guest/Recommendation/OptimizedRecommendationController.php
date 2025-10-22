<?php

namespace App\Http\Controllers\Api\App\Guest\Recommendation;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Client\Recommendation\OptimizedRecommendationService;
use Illuminate\Http\Request;

class OptimizedRecommendationController extends Controller
{


    public function __construct(private OptimizedRecommendationService $recommendationService) {}


    /**
     * Get recommendations - collaborative first, then fallback to general
     */
    public function getRecommendations(Request $request)
    {
        return $this->recommendationService->getRecommendations($request);
    }

    /**
     * Get top-rated products
     */
    public function getTopRatedProducts(Request $request)
    {
        return $this->recommendationService->getTopRatedProducts($request);
    }

    /**
     * Get most-ordered products
     */
    public function getMostOrderedProducts(Request $request)
    {
        return $this->recommendationService->getMostOrderedProducts($request);
    }
}


