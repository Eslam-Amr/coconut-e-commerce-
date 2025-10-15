<?php

namespace App\Services\Api\App\Client\Product;

use App\Services\Utilities\RecommendationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ProductService
{
    use ApiResponseTrait;

    public function __construct(private RecommendationService $recommendationService)
    {
    }

    public function recommendations(Request $request)
    {
        try {
            $limit = (int)($request->query('limit', 12));
            $limit = $limit > 0 ? $limit : 12;

            $user = $request->user();
            if (!$user) {
                return $this->errorResponse('Unauthorized', [], 401);
            }

            $recommended = $this->recommendationService->getRecommendations($user->id, $limit);

            $recommended->load(['brand.translations', 'category.translations', 'translations', 'media'])
                ->loadAvg('reviews', 'rating')
                ->loadCount(['orderItems', 'wishlists', 'reviews']);

            return $this->successResponse($recommended, 'Recommendations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve recommendations', ['error' => $e->getMessage()]);
        }
    }
}


