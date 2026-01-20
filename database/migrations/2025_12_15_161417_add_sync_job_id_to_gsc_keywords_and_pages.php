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
        // Add sync_job_id to ane_gsc_keywords
        Schema::table('ane_gsc_keywords', function (Blueprint $table) {
            $table->foreignId('sync_job_id')->nullable()->after('connection_id')
                ->constrained('ane_gsc_sync_jobs')->onDelete('cascade');
            $table->index('sync_job_id');
        });

        // Add sync_job_id to ane_gsc_pages
        Schema::table('ane_gsc_pages', function (Blueprint $table) {
            $table->foreignId('sync_job_id')->nullable()->after('connection_id')
                ->constrained('ane_gsc_sync_jobs')->onDelete('cascade');
            $table->index('sync_job_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_gsc_keywords', function (Blueprint $table) {
            $table->dropForeign(['sync_job_id']);
            $table->dropColumn('sync_job_id');
        });

        Schema::table('ane_gsc_pages', function (Blueprint $table) {
            $table->dropForeign(['sync_job_id']);
            $table->dropColumn('sync_job_id');
        });
    }
};
