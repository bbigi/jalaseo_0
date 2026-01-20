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
        Schema::table('ane_templates', function (Blueprint $table) {
            // Drop old category column
            $table->dropColumn('category');

            // Add foreign key to template_categories
            $table->foreignId('category_id')->nullable()->after('name')->constrained('ane_template_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_templates', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            // Restore old category column
            $table->string('category', 50)->nullable();
        });
    }
};
