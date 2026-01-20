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
        Schema::table('ane_posts', function (Blueprint $table) {
            $table->json('ai_generated_tags')->nullable()->after('seo_meta_description')->comment('AI-generated tag names (array of strings) - converted to tag IDs when publishing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_posts', function (Blueprint $table) {
            $table->dropColumn('ai_generated_tags');
        });
    }
};
