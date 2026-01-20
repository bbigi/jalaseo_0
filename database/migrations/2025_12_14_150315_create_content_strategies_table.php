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
        Schema::create('ane_content_strategies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('search_id');
            $table->enum('strategy_type', ['roundup', 'hybrid', 'deep_dive_all']);
            $table->json('selected_topics')->comment('Array of result IDs');
            $table->json('ranked_topics')->nullable()->comment('AI-ranked with scores');
            $table->enum('status', ['pending', 'generating', 'completed', 'failed'])->default('pending');
            $table->timestamps();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('cascade');
            $table->foreign('search_id')->references('id')->on('ane_social_searches')->onDelete('cascade');

            // Indices
            $table->index('created_by');
            $table->index('search_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_content_strategies');
    }
};
