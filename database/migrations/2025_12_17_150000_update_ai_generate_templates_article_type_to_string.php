<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ane_ai_generate_templates')) {
            DB::statement("ALTER TABLE `ane_ai_generate_templates` MODIFY COLUMN `article_type` VARCHAR(255) NULL DEFAULT 'blog'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ane_ai_generate_templates')) {
            DB::statement("ALTER TABLE `ane_ai_generate_templates` MODIFY COLUMN `article_type` ENUM('blog','news','listicle','tutorial','review') NOT NULL DEFAULT 'blog'");
        }
    }
};
