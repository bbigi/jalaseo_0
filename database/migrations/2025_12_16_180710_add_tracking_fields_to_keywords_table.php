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
        Schema::table('ane_keywords', function (Blueprint $table) {
            // Priority & tracking (NEW columns only)
            $table->enum('priority', ['high', 'medium', 'low', 'none'])
                ->default('medium')
                ->after('source');

            $table->enum('tracking_status', ['active', 'paused', 'archived'])
                ->default('active')
                ->after('priority');

            // Position tracking
            $table->integer('target_position')->nullable()->after('tracking_status');
            $table->integer('current_position')->nullable()->after('target_position');

            // Additional metadata
            $table->boolean('has_deep_research_data')->default(false)->after('current_position');
            $table->integer('competition_index')->nullable()->after('has_deep_research_data');
            $table->string('intent', 50)->nullable()->after('competition_index');

            // Timestamps
            $table->timestamp('first_tracked_at')->nullable()->after('intent');
            $table->timestamp('last_synced_at')->nullable()->after('first_tracked_at');

            // Indexes
            $table->index(['website_id', 'priority']);
            $table->index(['website_id', 'tracking_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_keywords', function (Blueprint $table) {
            $table->dropIndex(['website_id', 'tracking_status']);
            $table->dropIndex(['website_id', 'priority']);

            $table->dropColumn([
                'priority',
                'tracking_status',
                'target_position',
                'current_position',
                'has_deep_research_data',
                'competition_index',
                'intent',
                'first_tracked_at',
                'last_synced_at',
            ]);
        });
    }
};
