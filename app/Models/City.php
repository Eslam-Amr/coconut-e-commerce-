<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory, Translatable;

    protected $fillable = [
        'name',
        'country_id',
    ];
    public $translatedAttributes = [
        'name',
    ];

    protected $hidden = [
        'translations',
    ];


    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CityTranslation::class);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
