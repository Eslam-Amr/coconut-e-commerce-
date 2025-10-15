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
        Schema::create('user_product_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Points system
            $table->integer('total_points')->default(0);
            
            // Interaction details (optional for analysis)
            $table->integer('view_count')->default(0);
            $table->integer('wishlist_count')->default(0); // 0 or 1 usually
            $table->integer('purchase_count')->default(0);
            $table->integer('review_count')->default(0);
            $table->integer('last_rating')->nullable();
            
            // Time tracking
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->unique(['user_id', 'product_id'], 'unique_user_product');
            $table->index(['user_id', 'total_points'], 'idx_user_points');
            $table->index(['product_id', 'total_points'], 'idx_product_points');
            $table->index('last_interaction_at', 'idx_last_interaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_product_interactions');
    }
};