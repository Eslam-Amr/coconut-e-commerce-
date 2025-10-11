<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // Polymorphic target: can point to products or categories
            $table->morphs('flashable');

            $table->decimal('discount')->default(0)->max(100)->min(0);
            $table->string('max_limit')->nullable();
            $table->string('count')->default(0);

            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('flash_sale_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_id')->constrained('flash_sales')->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('title');
            $table->timestamps();
            $table->unique(['flash_sale_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sale_translations');
            Schema::dropIfExists('flash_sales');
    }
};


