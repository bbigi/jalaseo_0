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
        Schema::create('ane_ga4_referral_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('ane_websites')->onDelete('cascade');
            $table->foreignId('connection_id')->constrained('ane_ga4_connections')->onDelete('cascade');
            $table->date('date');
            $table->string('source', 500); // Referring domain (e.g., "google.com", "facebook.com")
            $table->string('medium', 100); // referral, organic, direct, social
            $table->integer('users')->default(0);
            $table->integer('sessions')->default(0);
            $table->integer('page_views')->default(0);
            $table->decimal('avg_session_duration', 10, 2)->default(0);
            $table->decimal('bounce_rate', 5, 4)->default(0);
            $table->timestamps();

            // Unique constraint
            $table->unique(['website_id', 'source', 'medium', 'date'], 'uk_source_date');

            // Indexes
            $table->index(['connection_id', 'date']);
            $table->index('date');
            $table->index(['source', 'medium']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_ga4_referral_sources');
    }
};
