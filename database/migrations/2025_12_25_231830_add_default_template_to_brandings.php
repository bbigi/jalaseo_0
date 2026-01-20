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
        Schema::table('ane_user_brandings', function (Blueprint $table) {
            // Check if column exists first
            if (!Schema::hasColumn('ane_user_brandings', 'default_template_id')) {
                $table->unsignedBigInteger('default_template_id')->nullable()->after('default_font');
            }
        });

        // Add foreign key in separate statement (after column is confirmed to exist)
        Schema::table('ane_user_brandings', function (Blueprint $table) {
            $table->foreign('default_template_id')
                  ->references('id')
                  ->on('ane_templates')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_user_brandings', function (Blueprint $table) {
            $table->dropForeign(['default_template_id']);
            $table->dropColumn('default_template_id');
        });
    }
};
