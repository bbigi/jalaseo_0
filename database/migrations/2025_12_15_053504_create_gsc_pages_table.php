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
        Schema::create('ane_gsc_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('ane_websites')->onDelete('cascade');
            $table->foreignId('connection_id')->constrained('ane_gsc_connections')->onDelete('cascade');
            $table->date('date');
            $table->text('page_url');
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->decimal('ctr', 5, 4)->default(0);
            $table->decimal('position', 5, 2)->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['website_id', 'date']);
            $table->index('date');
            $table->index('clicks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_gsc_pages');
    }
};
