<?php

namespace App\Services\Utilities;

use App\Models\Settings;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Collection;

class CartCalculationService
{
    private Settings $settings;

    public function __construct()
    {
        $this->settings = Settings::current();
    }

    /**
     * Calculate total cart price including shipping, VAT, and tax using address ID
     */
    public function calculateCartTotalByAddress(int $userId, ?int $addressId = null): array
    {
        $cart = Cart::where('user_id', $userId)->with('items.product')->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return $this->emptyCartResponse();
        }

        // Get address coordinates
        $userLatitude = null;
        $userLongitude = null;
        
        if ($addressId) {
            $address = Address::where('id', $addressId)
                ->where('user_id', $userId)
                ->first();
            User::find($userId)->update(['default_address_id' => $addressId]);
            if ($address && $address->hasCoordinates()) {
                $userLatitude = $address->latitude;
                $userLongitude = $address->longitude;
            }
        } else {
            // Use default address
            $defaultAddress = Address::find(auth()->user()->default_address_id);
                        if ($defaultAddress && $defaultAddress->hasCoordinates()) {
                $userLatitude = $defaultAddress->latitude;
                $userLongitude = $defaultAddress->longitude;
            }
        }

        // Calculate subtotal
        $subtotal = $cart->subtotal;
        
        // Calculate shipping
        $shippingCost = $this->calculateShippingCost($userLatitude, $userLongitude);
        
        // Calculate VAT
        $vatAmount = $this->calculateVAT($subtotal);
        
        // Calculate tax
        $taxAmount = $this->calculateTax($subtotal);
        
        // Calculate total
        $total = $subtotal + $shippingCost + $vatAmount + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'shipping_cost' => round($shippingCost, 2),
            'vat_rate' => $this->settings->vat_rate,
            'vat_amount' => round($vatAmount, 2),
            'tax_rate' => $this->settings->tax_rate,
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
            'currency' => 'EGP',
            'breakdown' => [
                'items_count' => $cart->items->count(),
                'total_quantity' => $cart->items->sum('quantity'),
                'shipping_distance_km' => $this->calculateDistance($userLatitude, $userLongitude),
                'company_location' => $this->settings->location,
                'user_location' => $userLatitude && $userLongitude ? [
                    'latitude' => $userLatitude,
                    'longitude' => $userLongitude,
                ] : null,
                'address_source' => $this->getAddressSourceByAddressId($userId, $addressId),
                'address_id' => $addressId,
            ]
        ];
    }

    /**
     * Calculate total cart price including shipping, VAT, and tax (legacy method)
     */
    public function calculateCartTotal(int $userId, ?float $userLatitude = null, ?float $userLongitude = null): array
    {
        $cart = Cart::where('user_id', $userId)->with('items.product')->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return $this->emptyCartResponse();
        }

        // Get user's default address if no coordinates provided
        if (!$userLatitude || !$userLongitude) {
            // dd(auth()->user()->default_address_id);
            // $defaultAddress = Address::getDefaultForUser($userId)->first();
            $defaultAddress = Address::find(auth()->user()->default_address_id);
            // dd(auth()->user()->default_address_id,$defaultAddress);
            if ($defaultAddress && $defaultAddress->hasCoordinates()) {
                $userLatitude = $defaultAddress->latitude;
                $userLongitude = $defaultAddress->longitude;
            }
        }

        // Calculate subtotal
        // $subtotal = $this->calculateSubtotal($cart->items);
        $subtotal = $cart->subtotal;
        
        // Calculate shipping
        // dd($userLatitude, $userLongitude);
        $shippingCost = $this->calculateShippingCost($userLatitude, $userLongitude);
        // dd($shippingCost);
        // Calculate VAT
        $vatAmount = $this->calculateVAT($subtotal);
        
        // Calculate tax
        $taxAmount = $this->calculateTax($subtotal);
        
        // Calculate total
        $total = $subtotal + $shippingCost + $vatAmount + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'shipping_cost' => round($shippingCost, 2),
            'vat_rate' => $this->settings->vat_rate,
            'vat_amount' => round($vatAmount, 2),
            'tax_rate' => $this->settings->tax_rate,
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
            'currency' => 'EGP', // You can make this configurable
            'breakdown' => [
                'items_count' => $cart->items->count(),
                'total_quantity' => $cart->items->sum('quantity'),
                'shipping_distance_km' => $this->calculateDistance($userLatitude, $userLongitude),
                'company_location' => $this->settings->location,
                'user_location' => $userLatitude && $userLongitude ? [
                    'latitude' => $userLatitude,
                    'longitude' => $userLongitude,
                ] : null,
                'address_source' => $this->getAddressSource($userId, $userLatitude, $userLongitude),
            ]
        ];
    }

    /**
     * Calculate subtotal from cart items
     */
    private function calculateSubtotal(Collection $items): float
    {
        return $items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
    }

    /**
     * Calculate shipping cost using Haversine formula
     */
    private function calculateShippingCost(?float $userLatitude, ?float $userLongitude): float
    {
        if (!$userLatitude || !$userLongitude) {
            // Return fixed shipping cost if no user location provided
            return 00.00; // Default shipping cost
        }

        $distance = $this->calculateDistance($userLatitude, $userLongitude);
        return $distance * $this->settings->kilo_shipping_price;
    }

    /**
     * Calculate distance between two points using optimized formula
     * Returns distance in kilometers
     */
    private function calculateDistance(?float $userLatitude, ?float $userLongitude): float
    {
        if (!$userLatitude || !$userLongitude) {
            return 0;
        }

        // Convert degrees to radians
        $lat1 = deg2rad($this->settings->latitude);
        $lon1 = deg2rad($this->settings->longitude);
        $lat2 = deg2rad($userLatitude);
        $lon2 = deg2rad($userLongitude);

        // Earth's radius in kilometers
        $earthRadius = 6371;

        // Calculate differences
        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        // Haversine formula
        $a = sin($dLat / 2) * sin($dLat / 2) + cos($lat1) * cos($lat2) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Calculate VAT amount
     */
    private function calculateVAT(float $subtotal): float
    {
        return $subtotal * $this->settings->vat_rate_decimal;
    }

    /**
     * Calculate tax amount
     */
    private function calculateTax(float $subtotal): float
    {
        return $subtotal * $this->settings->tax_rate_decimal;
    }

    /**
     * Return empty cart response
     */
    private function emptyCartResponse(): array
    {
        return [
            'subtotal' => 0.00,
            'shipping_cost' => 0.00,
            'vat_rate' => $this->settings->vat_rate,
            'vat_amount' => 0.00,
            'tax_rate' => $this->settings->tax_rate,
            'tax_amount' => 0.00,
            'total' => 0.00,
            'currency' => 'EGP',
            'breakdown' => [
                'items_count' => 0,
                'total_quantity' => 0,
                'shipping_distance_km' => 0,
                'company_location' => $this->settings->location,
                'user_location' => null,
                'address_source' => 'none',
            ]
        ];
    }

    /**
     * Determine the source of address coordinates
     */
    private function getAddressSource(int $userId, ?float $userLatitude, ?float $userLongitude): string
    {
        if (!$userLatitude || !$userLongitude) {
            return 'none';
        }

        $defaultAddress = Address::find(auth()->user()->default_address_id)->first();
        if ($defaultAddress && $defaultAddress->hasCoordinates()) {
            // Check if coordinates match default address
            if (abs($defaultAddress->latitude - $userLatitude) < 0.0001 && 
                abs($defaultAddress->longitude - $userLongitude) < 0.0001) {
                return 'default_address';
            }
        }

        return 'request_coordinates';
    }

    /**
     * Determine the source of address by address ID
     */
    private function getAddressSourceByAddressId(int $userId, ?int $addressId): string
    {
        if (!$addressId) {
            return 'default_address';
        }

        $address = Address::where('id', $addressId)
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            return 'invalid_address';
        }

        if (!$address->hasCoordinates()) {
            return 'address_no_coordinates';
        }

        return 'specific_address';
    }

}
