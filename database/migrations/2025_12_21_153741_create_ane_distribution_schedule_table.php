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
        Schema::create('ane_distribution_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('created_by');

            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();

            // WordPress/Platform integration
            $table->string('platform_post_id', 100)->nullable(); // External platform ID
            $table->json('publish_response')->nullable(); // API response for debugging
            $table->enum('publish_status', ['pending', 'success', 'failed'])->default('pending');

            $table->timestamps();

            // Foreign keys
            $table->foreign('asset_id')->references('id')->on('ane_social_assets')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('cascade');

            // Indices
            $table->index('asset_id');
            $table->index('created_by');
            $table->index(['created_by', 'scheduled_at']);
            $table->index(['created_by', 'publish_status']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_distribution_schedule');
    }
};
