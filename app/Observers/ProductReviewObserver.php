<?php

namespace App\Observers;

use App\Models\ProductReview;
use App\Services\Utilities\InteractionPointsService;

class ProductReviewObserver
{

    public function __construct(private InteractionPointsService $interactionService) {}

    /**
     * Handle the ProductReview "created" event.
     */
    public function created(ProductReview $productReview): void
    {
        // Record review interaction
        $this->interactionService->recordInteraction(
            $productReview->user_id,
            $productReview->product_id,
            'review',
            $productReview->rating
        );
    }

    /**
     * Handle the ProductReview "updated" event.
     */
    public function updated(ProductReview $productReview): void
    {
        // Check if rating changed
        if ($productReview->wasChanged('rating')) {
            $oldRating = $productReview->getOriginal('rating');
            $newRating = $productReview->rating;
            
            // Calculate the difference in points
            $oldPoints = $this->calculateReviewPoints($oldRating);
            $newPoints = $this->calculateReviewPoints($newRating);
            $pointsDifference = $newPoints - $oldPoints;
            
            // Record the point adjustment
            $this->interactionService->recordInteraction(
                $productReview->user_id,
                $productReview->product_id,
                'review_adjustment',
                $pointsDifference
            );
        }
    }

    /**
     * Calculate review points based on rating
     */
    private function calculateReviewPoints(int $rating): int
    {
        $basePoints = 3; // Base review points
        $ratingBonus = [
            5 => 2,
            4 => 1,
            3 => 0,
            2 => -1,
            1 => -2,
        ];
        
        return $basePoints + ($ratingBonus[$rating] ?? 0);
    }

    /**
     * Handle the ProductReview "deleted" event.
     */
    public function deleted(ProductReview $productReview): void
    {
        // Record review removal interaction
        $this->interactionService->recordInteraction(
            $productReview->user_id,
            $productReview->product_id,
            'review_remove',
            $productReview->rating
        );
    }
}
