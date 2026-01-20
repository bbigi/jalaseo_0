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
        Schema::create('ane_keyword_cache', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 500);
            $table->string('location', 50)->default('ID'); // Country code
            $table->string('language', 10)->default('id');
            $table->integer('search_volume')->nullable();
            $table->decimal('cpc', 8, 2)->nullable(); // Cost per click
            $table->decimal('competition', 3, 2)->nullable(); // 0.00 - 1.00
            $table->string('competition_level', 20)->nullable(); // low, medium, high
            $table->json('trend_data')->nullable(); // Monthly search volume trends
            $table->json('related_keywords')->nullable();
            $table->json('serp_features')->nullable(); // Featured snippet, PAA, etc.
            $table->integer('difficulty_score')->nullable(); // 0-100
            $table->dateTime('cached_at');
            $table->dateTime('expires_at'); // Cache for 30 days
            $table->string('data_source', 50)->default('rapidapi'); // rapidapi, dataforseo, serper
            $table->timestamps();

            // Unique key
            $table->unique(['keyword', 'location', 'language'], 'uk_keyword_location');

            // Indexes
            $table->index('expires_at');
            $table->index('search_volume');
            $table->index('difficulty_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_keyword_cache');
    }
};
