<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryTranslation extends Model
{
    public $timestamps = true;
    protected $fillable = [
        'country_id',
        'locale',
        'name',
    ];
}
