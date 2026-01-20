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
        Schema::create('ane_ga4_page_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('ane_websites')->onDelete('cascade');
            $table->foreignId('connection_id')->constrained('ane_ga4_connections')->onDelete('cascade');
            $table->date('date');
            $table->text('page_path'); // e.g., "/blog/seo-tips"
            $table->string('page_title')->nullable();
            $table->integer('page_views')->default(0);
            $table->integer('unique_page_views')->default(0);
            $table->decimal('avg_time_on_page', 10, 2)->default(0);
            $table->decimal('bounce_rate', 5, 4)->default(0);
            $table->decimal('exit_rate', 5, 4)->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['website_id', 'date']);
            $table->index('date');
            $table->index('page_views');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_ga4_page_stats');
    }
};
