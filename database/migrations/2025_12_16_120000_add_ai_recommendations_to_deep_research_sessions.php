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
        Schema::table('ane_deep_research_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('ane_deep_research_sessions', 'ai_recommendations')) {
                $table->json('ai_recommendations')->nullable()->after('ai_analysis');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_deep_research_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('ane_deep_research_sessions', 'ai_recommendations')) {
                $table->dropColumn('ai_recommendations');
            }
        });
    }
};
