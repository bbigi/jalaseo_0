<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ane_gsc_keywords', function (Blueprint $table) {
            // Drop existing unique index
            $table->dropUnique('uk_keyword_date');
        });

        Schema::table('ane_gsc_keywords', function (Blueprint $table) {
            // Extend keyword column to TEXT
            $table->text('keyword')->change();
        });

        // Re-create unique index with keyword prefix (191 chars max for utf8mb4)
        DB::statement('CREATE UNIQUE INDEX uk_keyword_date ON ane_gsc_keywords (website_id, keyword(191), date, country, device)');
    }

    public function down(): void
    {
        Schema::table('ane_gsc_keywords', function (Blueprint $table) {
            $table->dropIndex('uk_keyword_date');
        });

        Schema::table('ane_gsc_keywords', function (Blueprint $table) {
            $table->string('keyword', 500)->change();
        });

        Schema::table('ane_gsc_keywords', function (Blueprint $table) {
            $table->unique(['website_id', 'keyword', 'date', 'country', 'device'], 'uk_keyword_date');
        });
    }
};
