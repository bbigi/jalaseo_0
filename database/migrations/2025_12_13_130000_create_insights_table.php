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
        Schema::create('ane_insights', function (Blueprint $table) {
            $table->id();
            $table->string('topic', 255);
            $table->text('description')->nullable()->comment('Insight description or news snippet');
            $table->string('source', 100)->default('manual');
            $table->string('source_url', 1000)->nullable()->comment('Original source URL');
            $table->enum('source_type', ['manual', 'excel', 'ai_generated', 'google_trends', 'news_search', 'social_insight'])->default('manual')->comment('Source type of the insight');
            $table->enum('status', ['pending', 'in_progress'])->default('pending');
            $table->integer('article_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('ane_users')->onDelete('set null');
            $table->foreignId('website_id')->nullable()->constrained('ane_websites')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('source');
            $table->index('article_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_insights');
    }
};
