<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            // $table->string('name');
            $table->string('logo')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            // $table->softDeletes();
        });

        Schema::create('brand_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name');
            $table->timestamps();
            $table->unique(['brand_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_translations');
        Schema::dropIfExists('brands');
    }
};


