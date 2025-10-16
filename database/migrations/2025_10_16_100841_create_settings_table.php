<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->decimal('longitude', 10, 7)->nullable(); // Store longitude
            $table->decimal('latitude', 10, 7)->nullable();  // Store latitude
            $table->decimal('vat_rate', 5, 2)->default(0);   // VAT percentage (e.g., 15.00 for 15%)
            $table->decimal('tax_rate', 5, 2)->default(0);   // Tax percentage (e.g., 5.00 for 5%)
            $table->decimal('kilo_shipping_price', 8, 2)->default(0); // Price per kilometer for shipping
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            'company_name' => 'E-Commerce Store',
            'longitude' => 31.2001, // Default to Cairo, Egypt
            'latitude' => 29.9187,
            'vat_rate' => 14.00,    // 14% VAT
            'tax_rate' => 0.00,     // 0% additional tax
            'kilo_shipping_price' => 2.50, // 2.50 per kilometer
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};