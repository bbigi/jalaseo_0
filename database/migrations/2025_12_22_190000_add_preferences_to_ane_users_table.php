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
        Schema::table('ane_users', function (Blueprint $table) {
            $table->string('locale', 5)->default('en')->after('api_quota_limit');
            $table->string('timezone', 64)->default('UTC')->after('locale');
            $table->string('country_code', 5)->nullable()->after('timezone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_users', function (Blueprint $table) {
            $table->dropColumn(['locale', 'timezone', 'country_code']);
        });
    }
};
