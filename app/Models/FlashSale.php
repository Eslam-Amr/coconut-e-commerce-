<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlashSale extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    protected $fillable = [
        'discount',
        'max_limit',
        'count',
        'start_date',
        'end_date',
        'active',
        'flashable_id',
        'flashable_type',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'active' => 'boolean',
    ];

    public $translatedAttributes = [
        'title',
    ];

    public function flashable(): MorphTo
    {
        return $this->morphTo();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FlashSaleTranslation::class);
    }
}
