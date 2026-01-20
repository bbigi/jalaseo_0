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
        Schema::table('ane_gsc_keywords', function (Blueprint $table) {
            $table->string('keyword', 500)->change(); // Increase from 255 to 500
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_gsc_keywords', function (Blueprint $table) {
            $table->string('keyword', 255)->change();
        });
    }
};
