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
            $table->unsignedBigInteger('content_strategy_id')->nullable()->after('insight_id');
            $table->string('article_role', 20)->nullable()->after('content_strategy_id')->comment('roundup or deep_dive');
            $table->unsignedBigInteger('source_topic_id')->nullable()->after('article_role')->comment('FK to ane_social_results.id for deep dive');

            $table->foreign('content_strategy_id')->references('id')->on('ane_content_strategies')->onDelete('cascade');
            $table->index('article_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ane_posts', function (Blueprint $table) {
            $table->dropForeign(['content_strategy_id']);
            $table->dropColumn(['content_strategy_id', 'article_role', 'source_topic_id']);
        });
    }
};
