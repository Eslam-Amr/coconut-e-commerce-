<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'longitude',
        'latitude',
        'vat_rate',
        'tax_rate',
        'kilo_shipping_price',
    ];

    protected $casts = [
        'longitude' => 'decimal:7',
        'latitude' => 'decimal:7',
        'vat_rate' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'kilo_shipping_price' => 'decimal:2',
    ];

    /**
     * Request-level cache for settings instance
     */
    private static ?self $instance = null;

    /**
     * Get the current settings (singleton pattern with request-level caching)
     */
    public static function current(): self
    {
        // Return cached instance if exists
        if (static::$instance !== null) {
            return static::$instance;
        }

        // Query and cache for this request
        static::$instance = static::first() ?? static::create([
            'company_name' => 'E-Commerce Store',
            'longitude' => 31.2001,
            'latitude' => 29.9187,
            'vat_rate' => 14.00,
            'tax_rate' => 0.00,
            'kilo_shipping_price' => 2.50,
        ]);

        return static::$instance;
    }

    /**
     * Clear the request-level cache (useful for testing or after updates)
     */
    public static function clearCache(): void
    {
        static::$instance = null;
    }

    /**
     * Get company location as array
     */
    public function getLocationAttribute(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Get VAT rate as decimal (e.g., 0.14 for 14%)
     */
    public function getVatRateDecimalAttribute(): float
    {
        return $this->vat_rate / 100;
    }

    /**
     * Get tax rate as decimal (e.g., 0.05 for 5%)
     */
    public function getTaxRateDecimalAttribute(): float
    {
        return $this->tax_rate / 100;
    }
}