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
        Schema::table('ane_ai_generate_templates', function (Blueprint $table) {
            // Add ai_provider column before ai_model
            $table->string('ai_provider', 50)->nullable()->after('name');

            // Rename ai_model comment to clarify it now stores specific model
            // (no need to change column, just clarify usage)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_ai_generate_templates', function (Blueprint $table) {
            $table->dropColumn('ai_provider');
        });
    }
};
