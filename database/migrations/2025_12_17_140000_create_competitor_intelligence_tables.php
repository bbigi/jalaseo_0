<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ane_competitor_profiles')) {
            Schema::create('ane_competitor_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id');
                $table->unsignedBigInteger('created_by')->nullable();

                $table->string('domain', 255);
                $table->enum('added_by', ['manual', 'auto_discovery'])->default('manual');
                $table->string('country', 10)->default('Indonesia');
                $table->string('language', 10)->default('id');

                $table->integer('total_keywords')->nullable();
                $table->decimal('avg_rank', 5, 2)->nullable();
                $table->integer('traffic_estimate')->nullable();
                $table->decimal('market_share', 5, 2)->nullable();

                $table->json('intent_distribution')->nullable();
                $table->json('keyword_type_distribution')->nullable();
                $table->json('content_format_distribution')->nullable();

                $table->json('top_keywords')->nullable();
                $table->json('top_urls')->nullable();
                $table->json('ai_insights')->nullable();
                $table->json('comparison_data')->nullable();
                $table->timestamp('comparison_generated_at')->nullable();
                $table->enum('comparison_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');

                $table->enum('status', ['active', 'paused', 'archived'])->default('active');
                $table->timestamp('last_analyzed_at')->nullable();
                $table->text('notes')->nullable();

                $table->timestamps();

                $table->index('website_id');
                $table->index('domain');
                $table->index('status');
                $table->index('last_analyzed_at');
                $table->unique(['website_id', 'domain']);

                $table->foreign('website_id')->references('id')->on('ane_websites')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('set null');
            });
        } else {
            if (Schema::hasColumn('ane_competitor_profiles', 'our_website_id')) {
                Schema::table('ane_competitor_profiles', function (Blueprint $table) {
                    $table->dropColumn('our_website_id');
                });
            }
        }

        if (!Schema::hasTable('ane_competitor_analysis_sessions')) {
            Schema::create('ane_competitor_analysis_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 100)->unique();
                $table->unsignedBigInteger('competitor_id');
                $table->unsignedBigInteger('website_id');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
                $table->integer('progress')->default(0);
                $table->string('current_step', 255)->nullable();
                $table->text('error_message')->nullable();
                $table->json('raw_dataforseo_response')->nullable();
                $table->json('analysis_summary')->nullable();
                $table->timestamps();

                $table->index('session_id');
                $table->index('competitor_id');
                $table->index('website_id');
                $table->index('status');
                $table->index('created_at');

                $table->foreign('competitor_id')->references('id')->on('ane_competitor_profiles')->onDelete('cascade');
                $table->foreign('website_id')->references('id')->on('ane_websites')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('ane_competitor_keywords')) {
            Schema::create('ane_competitor_keywords', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('competitor_id');
                $table->string('session_id', 100)->nullable();
                $table->string('keyword', 255);
                $table->integer('position');
                $table->integer('search_volume')->nullable();
                $table->decimal('cpc', 10, 2)->nullable();
                $table->decimal('competition', 5, 2)->nullable();
                $table->decimal('traffic_share', 5, 2)->nullable();
                $table->string('url', 500)->nullable();
                $table->enum('intent', ['informational', 'commercial', 'transactional', 'navigational'])->nullable();
                $table->enum('keyword_type', ['short', 'medium', 'long'])->nullable();
                $table->string('content_format', 50)->nullable();
                $table->enum('source', ['dataforseo', 'manual'])->default('dataforseo');
                $table->timestamp('created_at')->useCurrent();

                $table->index('competitor_id');
                $table->index('session_id');
                $table->index('keyword');
                $table->index('position');
                $table->index('search_volume');
                $table->index('intent');
                $table->index('keyword_type');

                $table->foreign('competitor_id')->references('id')->on('ane_competitor_profiles')->onDelete('cascade');
                $table->foreign('session_id')->references('session_id')->on('ane_competitor_analysis_sessions')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('ane_competitor_raw')) {
            Schema::create('ane_competitor_raw', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('competitor_id');
                $table->string('session_id', 100)->nullable();
                $table->string('source', 50);
                $table->json('payload');
                $table->decimal('api_cost', 10, 4)->nullable();
                $table->integer('response_time_ms')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('competitor_id');
                $table->index('session_id');
                $table->index('source');
                $table->index('created_at');

                $table->foreign('competitor_id')->references('id')->on('ane_competitor_profiles')->onDelete('cascade');
                $table->foreign('session_id')->references('session_id')->on('ane_competitor_analysis_sessions')->onDelete('set null');
            });
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("
                CREATE OR REPLACE VIEW view_competitors_with_status AS
                SELECT
                    cp.*,
                    w.name as website_name,
                    w.url as website_url,
                    u.name as creator_name,
                    (SELECT status FROM ane_competitor_analysis_sessions
                     WHERE competitor_id = cp.id
                     ORDER BY created_at DESC LIMIT 1) as latest_analysis_status,
                    (SELECT progress FROM ane_competitor_analysis_sessions
                     WHERE competitor_id = cp.id
                     ORDER BY created_at DESC LIMIT 1) as latest_progress
                FROM ane_competitor_profiles cp
                LEFT JOIN ane_websites w ON cp.website_id = w.id
                LEFT JOIN ane_users u ON cp.created_by = u.id
            ");

            DB::statement("
                CREATE OR REPLACE VIEW view_top_competitors AS
                SELECT
                    cp.id,
                    cp.domain,
                    cp.website_id,
                    w.name as website_name,
                    cp.traffic_estimate,
                    cp.total_keywords,
                    cp.avg_rank,
                    cp.market_share,
                    cp.last_analyzed_at
                FROM ane_competitor_profiles cp
                LEFT JOIN ane_websites w ON cp.website_id = w.id
                WHERE cp.status = 'active'
                ORDER BY cp.traffic_estimate DESC
            ");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("DROP VIEW IF EXISTS view_top_competitors");
            DB::statement("DROP VIEW IF EXISTS view_competitors_with_status");
        }

        Schema::dropIfExists('ane_competitor_raw');
        Schema::dropIfExists('ane_competitor_keywords');
        Schema::dropIfExists('ane_competitor_analysis_sessions');
        Schema::dropIfExists('ane_competitor_profiles');
    }
};
