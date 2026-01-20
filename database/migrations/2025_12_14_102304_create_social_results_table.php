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
        Schema::create('ane_social_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('search_id')->comment('Reference to ane_social_searches');
            $table->string('platform', 20);
            $table->text('content')->comment('Trending content/title');
            $table->string('author', 255)->nullable();
            $table->string('link', 1000)->nullable()->comment('Source URL');
            $table->integer('engagement_likes')->default(0);
            $table->integer('engagement_comments')->default(0);
            $table->integer('engagement_shares')->default(0);
            $table->integer('engagement_total')->storedAs('engagement_likes + engagement_comments + engagement_shares')->comment('Total engagement score');
            $table->enum('sentiment', ['positive', 'negative', 'neutral', 'unknown'])->default('unknown');
            $table->json('hashtags')->nullable()->comment('Array of hashtags');
            $table->boolean('is_trending')->default(true);
            $table->timestamp('created_at')->useCurrent();

            // Indices
            $table->index('search_id');
            $table->index('platform');
            $table->index('is_trending');
            $table->index('engagement_total');

            // Foreign key
            $table->foreign('search_id')->references('id')->on('ane_social_searches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_social_results');
    }
};
