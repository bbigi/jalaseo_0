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
        Schema::create('ane_post_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('ane_posts')->onDelete('cascade');
            $table->foreignId('website_id')->constrained('ane_websites')->onDelete('cascade');
            $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->integer('wp_post_id')->nullable();
            $table->text('categories')->nullable()->comment('JSON array of category IDs');
            $table->text('tags')->nullable()->comment('JSON array of tag IDs');
            $table->text('wp_response')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('post_id');
            $table->index('website_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_post_targets');
    }
};
