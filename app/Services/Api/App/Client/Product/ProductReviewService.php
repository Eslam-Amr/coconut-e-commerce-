<?php

namespace App\Services\Api\App\Client\Product;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductReviewService
{
    use ApiResponseTrait;


    /**
     * Create a product review
     */
    public function createReview($request)
    {
        try {
            $user = $request->user();
            $productId = $request->input('product_id');
            $rating = $request->input('rating');
            $comment = $request->input('comment');

            // Check if user can review this product
            // $canReview = $this->canUserReviewProduct($productId, $user->id);
            // if (!$canReview['success']) {
            //     return $this->errorResponse($canReview['message'], 403);
            // }

            // Check if user already reviewed this product
            // $existingReview = ProductReview::where('user_id', $user->id)
            //     ->where('product_id', $productId)
            //     ->first();

            // if ($existingReview) {
            //     return $this->errorResponse('You have already reviewed this product', 400);
            // }

            DB::beginTransaction();

            try {
                // Create the review
                $review = ProductReview::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'rating' => $rating,
                    'comment' => $comment,
                ]);

                DB::commit();


                return $this->successResponse([
                    // 'review' => $review->load(['user:id,name', 'product:id,name'])
                    $review->load([
                        'user:id,name',
                        'product'
                    ])                    
                ], __('messages.created_successfully'));

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to create product review', [
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Exception in createReview', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse(__('messages.creation_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Get reviews for a specific product (public endpoint)
     */
    public function getProductReviews($productId, Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $reviews = ProductReview::where('product_id', $productId)
                ->with(['user:id,name'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Calculate average rating
            $averageRating = ProductReview::where('product_id', $productId)->avg('rating');
            $totalReviews = ProductReview::where('product_id', $productId)->count();

            return $this->successResponse([
                'reviews' => $reviews,
                'average_rating' => round($averageRating, 2),
                'total_reviews' => $totalReviews
            ]);

        } catch (\Exception $e) {
            Log::error('Exception in getProductReviews', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse(__('messages.retrieval_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $reviews = ProductReview::where('user_id', $user->id)
                ->with(['product'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->successResponse([
                'reviews' => $reviews
            ]);

        } catch (\Exception $e) {
            Log::error('Exception in getUserReviews', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse(__('messages.retrieval_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Update a review
     */
    public function updateReview($reviewId, $request)
    {
        try {
            $user = $request->user();
            $rating = $request->input('rating');
            $comment = $request->input('comment');

            $review = ProductReview::where('id', $reviewId)
                ->where('user_id', $user->id)
                ->first();

            if (!$review) {
                return $this->errorResponse(__('messages.not_found_or_unauthorized'), 404);
            }

            DB::beginTransaction();

            try {
                $review->update([
                    'rating' => $rating,
                    'comment' => $comment,
                ]);

                DB::commit();

                Log::info('Product review updated successfully', [
                    'review_id' => $review->id,
                    'user_id' => $user->id,
                    'product_id' => $review->product_id,
                    'new_rating' => $rating
                ]);

                return $this->successResponse([
                    'review' => $review->load(['user:id,name', 'product'])
                ], __('messages.updated_successfully'));

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to update product review', [
                    'review_id' => $reviewId,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Exception in updateReview', [
                'review_id' => $reviewId,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse(__('messages.update_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Delete a review
     */
    public function deleteReview($reviewId)
    {
        try {
            $user = request()->user();

            $review = ProductReview::where('id', $reviewId)
                ->where('user_id', $user->id)
                ->first();

            if (!$review) {
                return $this->errorResponse(__('messages.not_found_or_unauthorized'), 404);
            }

            $productId = $review->product_id;

            DB::beginTransaction();

            try {
                $review->delete();

                DB::commit();

                Log::info('Product review deleted successfully', [
                    'review_id' => $reviewId,
                    'user_id' => $user->id,
                    'product_id' => $productId
                ]);

                return $this->successResponse([], __('messages.deleted_successfully'));

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to delete product review', [
                    'review_id' => $reviewId,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Exception in deleteReview', [
                'review_id' => $reviewId,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse(__('messages.deletion_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Check if user can review a product
     */
    public function canUserReviewProduct($productId, $userId = null)
    {
        
        try {
            $user = $userId ? \App\Models\User::find($userId) : request()->user();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Check if product exists
            $product = Product::find($productId);
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }

            // Check if user has completed an order containing this product
            $hasCompletedOrder = Order::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereHas('items', function ($query) use ($productId) {
                    $query->where('product_id', $productId);
                })
                ->exists();

            if (!$hasCompletedOrder) {
                return [
                    'success' => false,
                    'message' => 'You can only review products that you have purchased and received (completed orders)'
                ];
            }

            return [
                'success' => true,
                'message' => 'User can review this product'
            ];

        } catch (\Exception $e) {
            Log::error('Exception in canUserReviewProduct', [
                'product_id' => $productId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Error checking review eligibility: ' . $e->getMessage()
            ];
        }
    }

}
