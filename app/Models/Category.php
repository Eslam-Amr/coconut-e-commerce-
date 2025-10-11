<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Category extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    protected $fillable = [
        // 'name',
        'icon',
        'parent_id',
        'active',
    ];
    // protected $guarded = [];
    protected $hidden = [
        'translations', // Hide translations array, but keep localized values in main fields
    ];
    public $translatedAttributes = [
        'name',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }

    public function flashSales(): MorphMany
    {
        return $this->morphMany(FlashSale::class, 'flashable');
    }
}
