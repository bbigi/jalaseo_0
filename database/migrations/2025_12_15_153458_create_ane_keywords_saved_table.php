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
        Schema::create('ane_keywords_saved', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id');

            // Keyword Data
            $table->string('keyword_text', 500);
            $table->char('keyword_hash', 32)->comment('MD5 hash for quick lookup');

            // Metrics from GSC or Research
            $table->integer('search_volume')->nullable();
            $table->enum('competition_level', ['LOW', 'MEDIUM', 'HIGH'])->nullable();
            $table->integer('competition_index')->nullable();

            // Calculated Scores
            $table->float('priority_score')->nullable();
            $table->float('opportunity_score')->nullable();

            // Intent & Classification
            $table->enum('intent', ['informational', 'navigational', 'commercial', 'transactional'])->nullable();
            $table->enum('keyword_type', ['short', 'medium', 'long'])->nullable();

            // Usage Tracking
            $table->integer('insights_count')->default(0);
            $table->integer('articles_count')->default(0);
            $table->boolean('tracked_only')->default(true);

            // Tracking Status (from GSC)
            $table->float('current_position')->nullable();
            $table->float('best_position')->nullable();
            $table->boolean('is_tracking')->default(true);
            $table->dateTime('last_tracked_at')->nullable();

            // Metadata
            $table->enum('saved_from', ['research', 'manual', 'competitor', 'gsc'])->default('gsc');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['website_id', 'keyword_hash']);
            $table->index('website_id');
            $table->index('keyword_text');
            $table->index('priority_score');
            $table->index('is_tracking');

            // Foreign Keys
            $table->foreign('website_id')->references('id')->on('ane_websites')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_keywords_saved');
    }
};
