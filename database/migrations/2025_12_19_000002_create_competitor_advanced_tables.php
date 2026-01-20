<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ane_competitor_advanced_sessions')) {
            Schema::create('ane_competitor_advanced_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('competitor_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
                $table->integer('progress')->default(0);
                $table->string('error_message')->nullable();
                $table->json('summary_metrics')->nullable(); // direct from result[0].metrics.organic
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index(['competitor_id', 'status']);
                $table->index('user_id');
                $table->foreign('competitor_id')->references('id')->on('ane_competitor_profiles')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('ane_users')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('ane_competitor_ranked_keywords')) {
            Schema::create('ane_competitor_ranked_keywords', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('competitor_id');
                $table->unsignedBigInteger('session_id');
                $table->string('keyword', 255);
                $table->integer('search_volume')->nullable();
                $table->decimal('cpc', 10, 4)->nullable();
                $table->string('competition_level', 50)->nullable();
                $table->integer('rank_group')->nullable();
                $table->integer('rank_absolute')->nullable();
                $table->string('url', 500)->nullable();
                $table->decimal('etv', 12, 4)->nullable();
                $table->boolean('is_new')->default(false);
                $table->string('title', 500)->nullable();
                $table->string('source', 50)->default('dataforseo');
                $table->timestamps();

                $table->index(['competitor_id', 'keyword']);
                $table->index('session_id');
                $table->index('rank_group');
                $table->index('search_volume');

                $table->foreign('competitor_id')->references('id')->on('ane_competitor_profiles')->onDelete('cascade');
                $table->foreign('session_id')->references('id')->on('ane_competitor_advanced_sessions')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ane_competitor_ranked_keywords');
        Schema::dropIfExists('ane_competitor_advanced_sessions');
    }
};
