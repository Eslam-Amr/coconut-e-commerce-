<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('static_pages', function (Blueprint $table) {
            $table->id();
            // $table->string('title');
            // $table->longText('content');
            $table->timestamps();
        });

        Schema::create('static_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('static_page_id')->constrained('static_pages')->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('title');
            $table->text('content')->nullable();
            $table->timestamps();
            $table->unique(['static_page_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('static_page_translations');
        Schema::dropIfExists('static_pages');
    }
};


