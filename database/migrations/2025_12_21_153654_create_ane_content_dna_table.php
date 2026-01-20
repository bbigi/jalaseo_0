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
        Schema::create('ane_content_dna', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('created_by');

            // Core DNA fields
            $table->string('headline', 255);
            $table->string('subheadline', 500)->nullable();
            $table->text('hot_take')->nullable();
            $table->string('cta', 300)->nullable();

            // JSON arrays
            $table->json('key_points')->nullable();
            $table->json('keywords')->nullable();

            // Summaries
            $table->string('summary_short', 140)->nullable();
            $table->string('summary_medium', 300)->nullable();
            $table->text('summary_long')->nullable();

            // Metadata
            $table->enum('tone', ['neutral', 'informative', 'urgent', 'inspiring'])->default('neutral');
            $table->enum('intent', ['breaking', 'evergreen', 'refresh'])->default('evergreen');

            $table->timestamps();

            // Foreign keys
            $table->foreign('post_id')->references('id')->on('ane_posts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('cascade');

            // Indices
            $table->index('post_id');
            $table->index('created_by');
            $table->index(['created_by', 'intent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_content_dna');
    }
};
