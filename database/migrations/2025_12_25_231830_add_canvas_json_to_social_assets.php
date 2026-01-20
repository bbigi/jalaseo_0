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
        Schema::table('ane_social_assets', function (Blueprint $table) {
            $table->text('canvas_json')->nullable()->after('visual_path')->comment('Konva Stage JSON (rendered later)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_social_assets', function (Blueprint $table) {
            $table->dropColumn('canvas_json');
        });
    }
};
