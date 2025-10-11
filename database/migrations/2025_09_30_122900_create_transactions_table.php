<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            // $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded']);
            $table->enum('payment_method', ['credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash_on_delivery', 'wallet']);
            // $table->string('gateway_transaction_id')->nullable();
            // $table->json('gateway_response')->nullable();
            $table->decimal('tax', 8, 2)->default(0);
            $table->decimal('vat', 8, 2)->default(0);
            $table->decimal('tax_percentage', 8, 2)->default(0);
            $table->decimal('vat_percentage', 8, 2)->default(0);
            // $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
