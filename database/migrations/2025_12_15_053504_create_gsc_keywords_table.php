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
        Schema::create('ane_gsc_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('ane_websites')->onDelete('cascade');
            $table->foreignId('connection_id')->constrained('ane_gsc_connections')->onDelete('cascade');
            $table->date('date');
            $table->string('keyword', 500);
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->decimal('ctr', 5, 4)->default(0); // e.g., 0.0523 = 5.23%
            $table->decimal('position', 5, 2)->default(0); // e.g., 5.23
            $table->string('country', 10)->default('ALL'); // ID, US, ALL
            $table->string('device', 20)->default('ALL'); // DESKTOP, MOBILE, TABLET, ALL
            $table->text('page_url')->nullable();
            $table->timestamps();

            // Composite unique key
            $table->unique(['website_id', 'keyword', 'date', 'country', 'device'], 'uk_keyword_date');

            // Performance indexes
            $table->index('date');
            $table->index('clicks');
            $table->index('position');
            $table->index(['website_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_gsc_keywords');
    }
};
