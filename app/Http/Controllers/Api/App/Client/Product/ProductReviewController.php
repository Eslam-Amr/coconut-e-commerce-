<?php

namespace App\Http\Controllers\Api\App\Client\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\Client\Product\ProductReviewRequest;
use App\Services\Api\App\Client\Product\ProductReviewService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Traits\ApiResponseTrait;
use Illuminate\Routing\Controllers\Middleware;

class ProductReviewController extends Controller implements HasMiddleware
{
    use ApiResponseTrait;

    public static function middleware(): array
    {
        return [
            // 'client'
            new Middleware('client', except: ['getProductReviews']),

        ];
    }

    protected $productReviewService;

    public function __construct(ProductReviewService $productReviewService)
    {
        $this->productReviewService = $productReviewService;
    }

    /**
     * Create a product review
     */
    public function store(ProductReviewRequest $request)
    {
        return $this->productReviewService->createReview($request);
    }

    /**
     * Get reviews for a specific product
     */
    public function getProductReviews(Request $request, $productId)
    {
        return $this->productReviewService->getProductReviews($productId, $request);
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews(Request $request)
    {
        return $this->productReviewService->getUserReviews($request);
    }

    /**
     * Update a review
     */
    public function update(ProductReviewRequest $request, $reviewId)
    {
        return $this->productReviewService->updateReview($reviewId, $request);
    }

    /**
     * Delete a review
     */
    public function destroy($reviewId)
    {
        return $this->productReviewService->deleteReview($reviewId);
    }

    /**
     * Check if user can review a product
     */
    public function canReview(Request $request, $productId)
    {
        return $this->productReviewService->canUserReviewProduct($productId);
    }
}
