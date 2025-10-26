<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSaleTranslation extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'flash_sale_id',
        'locale',
        'title',
    ];
    
    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }
}