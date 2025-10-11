<?php

namespace App\Models;

use App\Traits\MediaTrait;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model implements TranslatableContract
{
    use HasFactory, Translatable,MediaTrait;

    protected $fillable = [
        'link',
        'start_date',
        'end_date',
        'active',
    ];

    protected $hidden = [
        'translations', // Hide translations array, but keep localized values in main fields
    ];

    public $translatedAttributes = [
        'title',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'active' => 'boolean',
    ];
}