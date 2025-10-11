<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasFactory ,Translatable;

    protected $fillable = [
        'city_id',
    ];

    public $translatedAttributes = [
        'name',
    ];
    protected $hidden = [
        'translations',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DistrictTranslation::class);
    }
}


