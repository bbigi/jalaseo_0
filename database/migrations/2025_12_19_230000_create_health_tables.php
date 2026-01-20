<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ane_health_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('ane_websites')->cascadeOnDelete();
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->decimal('health_score', 5, 2)->nullable();
            $table->decimal('indexing_score', 5, 2)->nullable();
            $table->decimal('performance_score', 5, 2)->nullable();
            $table->decimal('page_experience_score', 5, 2)->nullable();
            $table->json('summary_json')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'status']);
            $table->index(['website_id', 'created_at']);
        });

        Schema::create('ane_health_page_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('ane_health_sessions')->cascadeOnDelete();
            $table->string('url', 2048);
            $table->decimal('performance_score', 5, 2)->nullable();
            $table->integer('lcp_ms')->nullable();
            $table->integer('inp_ms')->nullable();
            $table->decimal('cls', 8, 4)->nullable();
            $table->integer('ttfb_ms')->nullable();
            $table->json('issues_json')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'created_at']);
        });

        DB::statement('CREATE INDEX idx_session_url ON ane_health_page_metrics (session_id, url(191))');
    }

    public function down(): void
    {
        Schema::dropIfExists('ane_health_page_metrics');
        Schema::dropIfExists('ane_health_sessions');
    }
};
