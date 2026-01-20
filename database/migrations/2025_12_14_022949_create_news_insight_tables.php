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
        // News searches history
        Schema::create('ane_news_searches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('source'); // Domain (detik.com, techcrunch.com, etc.)
            $table->string('theme')->nullable(); // Topic/theme (optional)
            $table->string('time_range')->default('week'); // today, week, month, year, all
            $table->integer('results_count')->default(0);
            $table->timestamps();

            // Foreign key
            $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('cascade');

            // Indices
            $table->index('created_by');
            $table->index('source');
            $table->index('created_at');
        });

        // News search results (cached)
        Schema::create('ane_news_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('search_id');
            $table->string('title');
            $table->text('url');
            $table->text('snippet')->nullable();
            $table->date('publish_date')->nullable();
            $table->integer('position')->default(0); // Search result position
            $table->timestamps();

            // Foreign key
            $table->foreign('search_id')->references('id')->on('ane_news_searches')->onDelete('cascade');

            // Indices
            $table->index('search_id');
            $table->index('publish_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_news_results');
        Schema::dropIfExists('ane_news_searches');
    }
};
