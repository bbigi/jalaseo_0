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
        Schema::create('ane_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insight_id')->nullable()->constrained('ane_insights')->onDelete('set null');
            $table->string('title', 255);
            $table->enum('article_type', ['blog', 'news'])->default('blog')->comment('Article format: blog or journalistic news');
            $table->longText('content')->nullable();
            $table->string('seo_focus_keyword', 150)->nullable();
            $table->string('seo_meta_description', 255)->nullable();
            $table->enum('source_type', ['manual', 'insight', 'ai_generated', 'google_trends', 'news_search', 'social_insight'])->default('manual')->comment('Source type of the post');
            $table->foreignId('created_by')->nullable()->constrained('ane_users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('insight_id');
            $table->index('source_type');
            $table->index('article_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_posts');
    }
};
