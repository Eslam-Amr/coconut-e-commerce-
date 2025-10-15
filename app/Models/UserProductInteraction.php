<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProductInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'total_points',
        'view_count',
        'wishlist_count',
        'purchase_count',
        'review_count',
        'last_rating',
        'last_interaction_at',
    ];

    protected $casts = [
        'last_interaction_at' => 'datetime',
        'total_points' => 'integer',
        'view_count' => 'integer',
        'wishlist_count' => 'integer',
        'purchase_count' => 'integer',
        'review_count' => 'integer',
        'last_rating' => 'integer',
    ];

    // Point values configuration
    const POINTS = [
        'view' => 1,
        'wishlist_add' => 5,
        'wishlist_remove' => -5,
        'purchase' => 10,
        'review' => 3,
        'rating_bonus' => [ // إضافي حسب التقييم
            5 => 2,
            4 => 1,
            3 => 0,
            2 => -1,
            1 => -2,
        ],
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get interactions with positive points
     */
    public function scopeWithPositivePoints($query)
    {
        return $query->where('total_points', '>', 0);
    }

    /**
     * Scope to get interactions by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get interactions by product
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to order by points descending
     */
    public function scopeOrderByPoints($query)
    {
        return $query->orderByDesc('total_points');
    }

    /**
     * Scope to order by last interaction
     */
    public function scopeOrderByLastInteraction($query)
    {
        return $query->orderByDesc('last_interaction_at');
    }
}