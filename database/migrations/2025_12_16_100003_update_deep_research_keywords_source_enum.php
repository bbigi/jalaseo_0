<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify source enum to include 'keyword_insight'
        DB::statement("ALTER TABLE `ane_deep_research_keywords` MODIFY COLUMN `source` ENUM('autocomplete', 'paa', 'related', 'serp_title', 'manual', 'keyword_insight') DEFAULT 'autocomplete'");
        
        // Fix intent enum to VARCHAR for flexibility
        DB::statement("ALTER TABLE `ane_deep_research_keywords` MODIFY COLUMN `intent` VARCHAR(50) NULL");
        
        // Change opportunity_score from TINYINT to DECIMAL for precision
        DB::statement("ALTER TABLE `ane_deep_research_keywords` MODIFY COLUMN `opportunity_score` DECIMAL(5,1) NULL");
        
        // Expand ai_recommended_action for longer recommendations
        DB::statement("ALTER TABLE `ane_deep_research_keywords` MODIFY COLUMN `ai_recommended_action` VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `ane_deep_research_keywords` MODIFY COLUMN `source` ENUM('autocomplete', 'paa', 'related', 'serp_title', 'manual') DEFAULT 'autocomplete'");
        DB::statement("ALTER TABLE `ane_deep_research_keywords` MODIFY COLUMN `intent` ENUM('informational', 'navigational', 'commercial', 'transactional') NULL");
    }
};
