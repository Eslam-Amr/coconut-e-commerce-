<?php

namespace App\Models;

use App\Traits\MediaTrait;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory, Translatable,MediaTrait;

    protected $fillable = [
        // 'name',
        // 'description',
        'category_id',
        'brand_id',
        'total_quantity',
        'base_price',
        'active',
    ];

    protected $hidden = [
        'translations',
    ];

    public $translatedAttributes = [
        'name',
        'description',
    ];

    protected $casts = [
        'description' => 'array',
        'active' => 'boolean',
        'base_price' => 'decimal:2',
        'total_quantity' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function flashSales(): MorphMany
    {
        return $this->morphMany(FlashSale::class, 'flashable');
    }


    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }
}
