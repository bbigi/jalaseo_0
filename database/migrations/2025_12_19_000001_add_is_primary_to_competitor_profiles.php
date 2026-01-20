<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ane_competitor_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('ane_competitor_profiles', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('status');
                $table->index('is_primary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ane_competitor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('ane_competitor_profiles', 'is_primary')) {
                $table->dropIndex(['is_primary']);
                $table->dropColumn('is_primary');
            }
        });
    }
};
