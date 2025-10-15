<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'default_address_id')) {
                $table->foreignId('default_address_id')->nullable()->after('user_type');
            }
            $table->foreign('default_address_id')
            ->references('id')
            ->on('addresses')
            ->nullOnDelete();
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('user_type');
            }
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['default_address_id']);
        });
    }
};


