<?php

namespace App\Models;

use App\Traits\MediaTrait;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model 
{
    use HasFactory, Translatable, MediaTrait;

    protected $fillable = [
        'logo',
        'active',
    ];

    protected $hidden = [
        'translations',
    ];

    public $translatedAttributes = [
        'name',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
