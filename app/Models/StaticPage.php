<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    protected $fillable = [
    ];

    protected $hidden = [
        'translations', // Hide translations array, but keep localized values in main fields
    ];

    public $translatedAttributes = [
        'title',
        'content',
    ];
}
