<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ane_pagespeed_runs', function (Blueprint $table) {
            $table->text('summary_text')->nullable()->after('resources_summary');
            $table->json('insights')->nullable()->after('summary_text');
        });
    }

    public function down(): void
    {
        Schema::table('ane_pagespeed_runs', function (Blueprint $table) {
            $table->dropColumn(['summary_text', 'insights']);
        });
    }
};
