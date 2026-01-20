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
        Schema::table('ane_news_searches', function (Blueprint $table) {
            $table->string('language', 2)->default('id')->after('time_range');
            $table->string('country', 2)->default('id')->after('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_news_searches', function (Blueprint $table) {
            $table->dropColumn(['language', 'country']);
        });
    }
};
