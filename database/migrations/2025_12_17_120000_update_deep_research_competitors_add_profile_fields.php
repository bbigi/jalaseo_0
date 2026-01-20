<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enrich competitor profile fields
        if (Schema::hasTable('ane_deep_research_competitors')) {
            Schema::table('ane_deep_research_competitors', function (Blueprint $table) {
                if (!Schema::hasColumn('ane_deep_research_competitors', 'top_pages')) {
                    $table->json('top_pages')->nullable()->after('keywords_data');
                }
                if (!Schema::hasColumn('ane_deep_research_competitors', 'top_keywords')) {
                    $table->json('top_keywords')->nullable()->after('top_pages');
                }
                if (!Schema::hasColumn('ane_deep_research_competitors', 'traffic_estimate')) {
                    $table->integer('traffic_estimate')->nullable()->after('top_keywords');
                }
                if (!Schema::hasColumn('ane_deep_research_competitors', 'featured_snippets')) {
                    $table->json('featured_snippets')->nullable()->after('traffic_estimate');
                }
                if (!Schema::hasColumn('ane_deep_research_competitors', 'gap_score')) {
                    $table->decimal('gap_score', 6, 2)->nullable()->after('featured_snippets');
                }
            });
        }

        // Flag keywords originating from gap analysis
        if (Schema::hasTable('ane_deep_research_keywords') && !Schema::hasColumn('ane_deep_research_keywords', 'from_gap')) {
            Schema::table('ane_deep_research_keywords', function (Blueprint $table) {
                $table->boolean('from_gap')->default(false)->after('save_reason');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ane_deep_research_competitors')) {
            Schema::table('ane_deep_research_competitors', function (Blueprint $table) {
                if (Schema::hasColumn('ane_deep_research_competitors', 'gap_score')) {
                    $table->dropColumn('gap_score');
                }
                if (Schema::hasColumn('ane_deep_research_competitors', 'featured_snippets')) {
                    $table->dropColumn('featured_snippets');
                }
                if (Schema::hasColumn('ane_deep_research_competitors', 'traffic_estimate')) {
                    $table->dropColumn('traffic_estimate');
                }
                if (Schema::hasColumn('ane_deep_research_competitors', 'top_keywords')) {
                    $table->dropColumn('top_keywords');
                }
                if (Schema::hasColumn('ane_deep_research_competitors', 'top_pages')) {
                    $table->dropColumn('top_pages');
                }
            });
        }

        if (Schema::hasTable('ane_deep_research_keywords') && Schema::hasColumn('ane_deep_research_keywords', 'from_gap')) {
            Schema::table('ane_deep_research_keywords', function (Blueprint $table) {
                $table->dropColumn('from_gap');
            });
        }
    }
};
