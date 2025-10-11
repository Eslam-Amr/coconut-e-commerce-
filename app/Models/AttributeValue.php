<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttributeValue extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    // protected $fillable = [
    //     'attribute_id',
    // ];
    protected $fillable = [
        'attribute_id',
        // 'ar',
        // 'en',
    ];
    

    public $translatedAttributes = [
        'value',
    ];

    protected $hidden = [
        'translations',
    ];


    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'variant_attribute_values',
            'attribute_value_id',
            'product_variant_id'
        );
    }
}
