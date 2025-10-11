<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    protected $fillable = [];
    // protected $guarded = [];

    public $translatedAttributes = [
        'name',
    ];

    protected $hidden = [
        'translations', // Hide translations array, but keep localized values in main fields
    ];

 

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

}
