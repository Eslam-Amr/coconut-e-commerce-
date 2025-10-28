<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'longitude',
        'latitude',
        'subtotal',
        'discount',
        // 'shipping_fee',
        'shipping_price',
        'tax',
        'vat',
        'total',
        'status',
        'payment_method',
        'payment_status',
        // 'currency',
        'shipping_address',
        'billing_address',
    ];

    protected $casts = [
        'longitude' => 'decimal:7',
        'latitude' => 'decimal:7',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'shipping_price' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'status' => OrderStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function voucherUsages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

}
