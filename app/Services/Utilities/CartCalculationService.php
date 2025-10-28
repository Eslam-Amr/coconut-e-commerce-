<?php

namespace App\Services\Utilities;

use App\Models\Settings;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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
            $defaultAddress = Address::find(Auth::user()->default_address_id);
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
        
        // Calculate formatted totals with proper rounding
        $totals = $this->calculateFormattedTotals($subtotal, $shippingCost, $vatAmount, $taxAmount, $cart->discount);

        // Update cart with shipping price
        $cart->update(['shipping_price' => $totals['shipping_price']]);

        return [
            'subtotal' => $totals['subtotal'],
            'shipping_cost' => $totals['shipping_cost'],
            'shipping_price' => $totals['shipping_price'], // Same as shipping_cost for compatibility
            'vat_rate' => $this->settings->vat_rate,
            'vat_amount' => $totals['vat_amount'],
            'tax_rate' => $this->settings->tax_rate,
            'tax_amount' => $totals['tax_amount'],
            'total_without_shipping' => $totals['total_without_shipping'], // Total without shipping
            'total' => $totals['total'], // Total with shipping
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
            // dd(Auth::user()->default_address_id);
            // $defaultAddress = Address::getDefaultForUser($userId)->first();
            $defaultAddress = Address::find(Auth::user()->default_address_id);
            // dd(Auth::user()->default_address_id,$defaultAddress);
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
        
        // Calculate formatted totals with proper rounding
        $totals = $this->calculateFormattedTotals($subtotal, $shippingCost, $vatAmount, $taxAmount, $cart->discount);

        // Update cart with shipping price
        $cart->update(['shipping_price' => $totals['shipping_price']]);

        return [
            'subtotal' => $totals['subtotal'],
            'shipping_cost' => $totals['shipping_cost'],
            'shipping_price' => $totals['shipping_price'], // Same as shipping_cost for compatibility
            'vat_rate' => $this->settings->vat_rate,
            'vat_amount' => $totals['vat_amount'],
            'tax_rate' => $this->settings->tax_rate,
            'tax_amount' => $totals['tax_amount'],
            'total_without_shipping' => $totals['total_without_shipping'], // Total without shipping
            'total' => $totals['total'], // Total with shipping
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
     * Calculate and format cart totals with proper rounding
     */
    private function calculateFormattedTotals($subtotal, $shippingCost, $vatAmount, $taxAmount, $discount = 0): array
    {
        // Round all components first
        $roundedSubtotal = round($subtotal, 2);
        $roundedShippingCost = round($shippingCost, 2);
        $roundedVatAmount = round($vatAmount, 2);
        $roundedTaxAmount = round($taxAmount, 2);
        $roundedDiscount = round($discount, 2);
        
        // Calculate totals with rounded values
        $totalWithoutShipping = $roundedSubtotal - $roundedDiscount;
        $total = $totalWithoutShipping + $roundedShippingCost + $roundedVatAmount + $roundedTaxAmount;
        
        return [
            'subtotal' => $roundedSubtotal,
            'shipping_cost' => $roundedShippingCost,
            'shipping_price' => $roundedShippingCost,
            'vat_amount' => $roundedVatAmount,
            'tax_amount' => $roundedTaxAmount,
            'total_without_shipping' => $totalWithoutShipping,
            'total' => round($total, 2)
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
    public function calculateShippingCost(?float $userLatitude, ?float $userLongitude): float
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
    public function calculateVAT(float $subtotal): float
    {
        return $subtotal * $this->settings->vat_rate_decimal;
    }

    /**
     * Calculate tax amount
     */
    public function calculateTax(float $subtotal): float
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

        $defaultAddress = Address::find(Auth::user()->default_address_id)->first();
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
