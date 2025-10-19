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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('description')->nullable()->after('amount');
            $table->decimal('balance_after', 12, 2)->nullable()->after('description');
            $table->string('transaction_id')->unique()->after('balance_after');
            $table->string('payment_method')->nullable()->after('transaction_id');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending')->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['description', 'balance_after', 'transaction_id', 'payment_method', 'status']);
        });
    }
};
