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
        Schema::create('ane_social_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('created_by');

            // Channel & type
            $table->enum('channel', ['instagram', 'facebook', 'twitter', 'linkedin']);
            $table->enum('asset_type', ['feed', 'story', 'thread'])->default('feed');

            // Copy fields
            $table->text('copy_text');
            $table->integer('copy_version')->default(1);

            // Visual fields
            $table->string('visual_path', 500)->nullable();
            $table->string('visual_size', 20)->nullable(); // e.g., "4:5", "9:16", "1:1"
            $table->json('visual_metadata')->nullable(); // Template config

            // Status
            $table->enum('status', ['draft', 'ready', 'scheduled', 'published'])->default('draft');

            $table->timestamps();

            // Foreign keys
            $table->foreign('post_id')->references('id')->on('ane_posts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('cascade');

            // Indices
            $table->index('post_id');
            $table->index('created_by');
            $table->index(['created_by', 'channel']);
            $table->index(['created_by', 'status']);
            $table->index(['post_id', 'channel', 'asset_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_social_assets');
    }
};
