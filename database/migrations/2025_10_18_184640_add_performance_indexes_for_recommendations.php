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
        Schema::table('products', function (Blueprint $table) {
            // Composite index for active products with stock
            $table->index(['active', 'total_quantity'], 'idx_products_active_stock');
            
            // Index for category and brand filtering
            $table->index(['category_id', 'active'], 'idx_products_category_active');
            $table->index(['brand_id', 'active'], 'idx_products_brand_active');
        });

        Schema::table('user_product_interactions', function (Blueprint $table) {
            // Composite index for user-product queries
            $table->index(['user_id', 'product_id'], 'idx_upi_user_product');
            $table->index(['user_id', 'total_points'], 'idx_upi_user_points');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            // Index for rating aggregations
            $table->index(['product_id', 'rating'], 'idx_reviews_product_rating');
        });

        Schema::table('order_items', function (Blueprint $table) {
            // Index for order count aggregations
            $table->index('product_id', 'idx_order_items_product');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            // Index for wishlist count aggregations
            $table->index('product_id', 'idx_wishlists_product');
        });

        Schema::table('product_translations', function (Blueprint $table) {
            // Index for translation lookups
            $table->index(['product_id', 'locale'], 'idx_translations_product_locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_active_stock');
            $table->dropIndex('idx_products_category_active');
            $table->dropIndex('idx_products_brand_active');
        });

        Schema::table('user_product_interactions', function (Blueprint $table) {
            $table->dropIndex('idx_upi_user_product');
            $table->dropIndex('idx_upi_user_points');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_product_rating');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_product');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropIndex('idx_wishlists_product');
        });

        Schema::table('product_translations', function (Blueprint $table) {
            $table->dropIndex('idx_translations_product_locale');
        });
    }
};