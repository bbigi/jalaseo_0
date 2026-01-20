<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deep Research Sessions
        if (!Schema::hasTable('ane_deep_research_sessions')) {
            Schema::create('ane_deep_research_sessions', function (Blueprint $table) {
            $table->id();
            $table->char('session_id', 36)->unique();
            $table->unsignedBigInteger('website_id');
            $table->unsignedBigInteger('created_by')->nullable();
            
            // Seed Input
            $table->string('seed_keyword', 255);
            $table->string('seed_keyword_hash', 32)->index();
            
            // Processing Status
            $table->enum('status', ['pending', 'processing', 'paused', 'completed', 'failed', 'expired'])->default('pending');
            $table->tinyInteger('progress')->default(0);
            $table->string('current_step', 50)->nullable();
            $table->text('error_message')->nullable();
            
            // Research Results (JSON)
            $table->json('autocomplete_results')->nullable();
            $table->json('selected_keywords')->nullable();
            $table->json('serp_snapshot')->nullable();
            $table->json('paa_data')->nullable();
            $table->json('related_searches')->nullable();
            $table->json('competitors')->nullable();
            
            // AI Analysis Results
            $table->json('ai_analysis')->nullable();
            $table->json('content_blueprint')->nullable();
            
            // Statistics
            $table->integer('keywords_count')->default(0);
            $table->integer('total_competitors_found')->default(0);
            $table->integer('auto_saved_keywords_count')->default(0);
            
            // API Costs
            $table->decimal('total_api_cost', 10, 4)->default(0);
            
            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('website_id')->references('id')->on('ane_websites')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('set null');
            
            // Indexes
            $table->index(['website_id', 'status']);
            $table->index('created_at');
        });
        }
        
        // Deep Research Keywords
        if (!Schema::hasTable('ane_deep_research_keywords')) {
            Schema::create('ane_deep_research_keywords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deep_research_id');
            
            // Keyword Data
            $table->string('keyword', 500);
            $table->string('keyword_hash', 32)->index();
            $table->enum('source', ['autocomplete', 'paa', 'related', 'serp_title', 'manual'])->default('autocomplete');
            
            // Metrics
            $table->integer('volume')->nullable();
            $table->enum('competition_level', ['LOW', 'MEDIUM', 'HIGH'])->nullable();
            $table->integer('competition_index')->nullable();
            $table->decimal('cpc', 8, 2)->nullable();
            
            // Scores
            $table->tinyInteger('difficulty_score')->nullable();
            $table->tinyInteger('opportunity_score')->nullable();
            $table->tinyInteger('ai_priority_score')->nullable();
            
            // Intent & Classification
            $table->enum('intent', ['informational', 'navigational', 'commercial', 'transactional'])->nullable();
            $table->string('ai_recommended_action', 100)->nullable();
            
            // Save Status
            $table->enum('save_status', ['not_saved', 'user_saved', 'auto_saved', 'skipped'])->default('not_saved');
            $table->string('save_reason', 100)->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign Keys
            $table->foreign('deep_research_id')->references('id')->on('ane_deep_research_sessions')->onDelete('cascade');
            
            // Indexes
            $table->index('ai_priority_score');
            $table->index('opportunity_score');
            $table->unique(['deep_research_id', 'keyword_hash']);
        });
        }
        
        // Deep Research Competitors
        if (!Schema::hasTable('ane_deep_research_competitors')) {
            Schema::create('ane_deep_research_competitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deep_research_id');
            
            // Competitor Data
            $table->string('competitor_domain', 255);
            $table->enum('competitor_type', ['direct', 'indirect', 'content', 'authority'])->default('direct');
            
            // Ranking Stats
            $table->integer('appears_in_keywords')->default(0);
            $table->decimal('avg_rank', 5, 2)->nullable();
            $table->integer('keyword_count')->default(0);
            $table->boolean('keywords_profiled')->default(false);
            
            // Keyword Gap Analysis
            $table->json('keywords_data')->nullable();
            $table->json('keyword_gap')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign Keys
            $table->foreign('deep_research_id')->references('id')->on('ane_deep_research_sessions')->onDelete('cascade');
            
            // Indexes
            $table->index('competitor_domain');
            $table->index('competitor_type');
            $table->unique(['deep_research_id', 'competitor_domain'], 'dr_comp_unique');
        });
        }
        
        // Add deep_research columns to keywords_saved (only if not exists)
        if (!Schema::hasColumn('ane_keywords_saved', 'deep_research_id')) {
            Schema::table('ane_keywords_saved', function (Blueprint $table) {
                $table->unsignedBigInteger('deep_research_id')->nullable()->after('saved_from');
                $table->tinyInteger('ai_priority_score')->nullable()->after('opportunity_score');
                $table->string('save_reason', 100)->nullable()->after('tracked_only');
                
                $table->foreign('deep_research_id')->references('id')->on('ane_deep_research_sessions')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ane_keywords_saved', function (Blueprint $table) {
            $table->dropForeign(['deep_research_id']);
            $table->dropColumn(['deep_research_id', 'ai_priority_score', 'save_reason']);
        });
        
        Schema::dropIfExists('ane_deep_research_competitors');
        Schema::dropIfExists('ane_deep_research_keywords');
        Schema::dropIfExists('ane_deep_research_sessions');
    }
};
