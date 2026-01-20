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
        Schema::create('ane_insight_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insight_id')->constrained('ane_insights')->onDelete('cascade');
            $table->foreignId('post_id')->constrained('ane_posts')->onDelete('cascade');
            $table->foreignId('website_id')->nullable()->constrained('ane_websites')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('ane_users')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->unique(['insight_id', 'post_id'], 'unique_insight_post');
            $table->index('insight_id');
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_insight_articles');
    }
};
