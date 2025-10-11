<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'discount',
        'max_limit',
        'count',
        'start_date',
        'end_date',
        'active',
        'flashable_id',
        'flashable_type',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'active' => 'boolean',
    ];

    public function flashable(): MorphTo
    {
        return $this->morphTo();
    }
}
