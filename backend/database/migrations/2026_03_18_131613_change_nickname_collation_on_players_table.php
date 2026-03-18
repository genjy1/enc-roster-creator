<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropUnique(['nickname']);
        });

        // Change to case-sensitive collation
        \Illuminate\Support\Facades\DB::statement(
            'ALTER TABLE players MODIFY nickname VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL'
        );
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement(
            'ALTER TABLE players MODIFY nickname VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL'
        );

        Schema::table('players', function (Blueprint $table) {
            $table->unique('nickname');
        });
    }
};
