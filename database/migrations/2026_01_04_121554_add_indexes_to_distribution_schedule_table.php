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
        Schema::table('ane_distribution_schedule', function (Blueprint $table) {
            // Add composite index for calendar queries (user + date range)
            $table->index(['created_by', 'scheduled_at'], 'idx_created_scheduled');

            // Add index for status filtering
            $table->index(['created_by', 'publish_status'], 'idx_created_status');

            // Add index for pending schedules (used by scheduler)
            $table->index(['publish_status', 'scheduled_at'], 'idx_status_scheduled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_distribution_schedule', function (Blueprint $table) {
            $table->dropIndex('idx_created_scheduled');
            $table->dropIndex('idx_created_status');
            $table->dropIndex('idx_status_scheduled');
        });
    }
};
