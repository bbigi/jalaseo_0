<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ane_deep_research_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('ane_deep_research_sessions', 'location')) {
                $table->string('location', 5)->default('ID')->after('seed_keyword_hash');
            }
            if (!Schema::hasColumn('ane_deep_research_sessions', 'language')) {
                $table->string('language', 10)->default('id')->after('location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ane_deep_research_sessions', function (Blueprint $table) {
            $table->dropColumn(['location', 'language']);
        });
    }
};
