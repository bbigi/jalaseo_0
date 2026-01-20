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
            $table->timestamp('queued_at')->nullable()->after('scheduled_at');
            $table->index('queued_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_distribution_schedule', function (Blueprint $table) {
            $table->dropIndex(['queued_at']);
            $table->dropColumn('queued_at');
        });
    }
};
