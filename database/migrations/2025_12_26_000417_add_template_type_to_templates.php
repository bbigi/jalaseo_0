<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ane_templates', function (Blueprint $table) {
            $table->enum('template_type', ['article', 'standalone'])
                  ->default('standalone')
                  ->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('ane_templates', function (Blueprint $table) {
            $table->dropColumn('template_type');
        });
    }
};
