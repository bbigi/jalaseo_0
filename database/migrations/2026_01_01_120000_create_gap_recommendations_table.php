<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ane_gap_recommendations')) {
            Schema::create('ane_gap_recommendations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id');
                $table->unsignedBigInteger('competitor_id')->nullable();
                $table->string('locale', 10)->default('en');
                $table->string('filter', 50)->nullable();
                $table->string('search', 200)->nullable();
                $table->longText('content');
                $table->string('provider', 50)->nullable();
                $table->string('model', 100)->nullable();
                $table->integer('tokens_used')->nullable();
                $table->timestamps();

                $table->foreign('website_id')->references('id')->on('ane_websites')->onDelete('cascade');
                $table->foreign('competitor_id')->references('id')->on('ane_competitor_profiles')->onDelete('set null');

                $table->index(['website_id', 'competitor_id', 'locale']);
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ane_gap_recommendations');
    }
};
