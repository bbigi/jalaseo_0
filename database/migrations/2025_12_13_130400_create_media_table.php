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
        Schema::create('ane_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('ane_posts')->onDelete('cascade');
            $table->foreignId('website_id')->nullable()->constrained('ane_websites')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('ane_users')->onDelete('set null');
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->string('file_type', 50)->nullable();
            $table->integer('file_size')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('caption', 255)->nullable();
            $table->string('source_type', 50)->default('manual');
            $table->text('ai_prompt')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('wp_media_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('post_id');
            $table->index('is_featured');
            $table->index('website_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_media');
    }
};
