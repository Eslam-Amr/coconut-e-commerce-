<?php

namespace App\Observers;

use App\Models\Wishlist;
use App\Services\Utilities\InteractionPointsService;

class WishlistObserver
{

    public function __construct( private InteractionPointsService $interactionService)
    {}

    /**
     * Handle the Wishlist "created" event.
     */
    public function created(Wishlist $wishlist): void
    {
        // Record wishlist add interaction
        $this->interactionService->recordInteraction(
            $wishlist->user_id,
            $wishlist->product_id,
            'wishlist_add'
        );
    }

    /**
     * Handle the Wishlist "deleted" event.
     */
    public function deleted(Wishlist $wishlist): void
    {
        // Record wishlist remove interaction
        $this->interactionService->recordInteraction(
            $wishlist->user_id,
            $wishlist->product_id,
            'wishlist_remove'
        );
    }
}