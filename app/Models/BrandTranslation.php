<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'brand_id',
        'locale',
        'name',
    ];
}


