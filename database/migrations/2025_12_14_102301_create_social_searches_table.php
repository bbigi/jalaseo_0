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
        Schema::create('ane_social_searches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('keyword', 255)->nullable()->comment('Optional search keyword');
            $table->string('platform', 20); // 'tiktok', 'twitter', 'google_trends'
            $table->string('country_code', 5)->default('ID')->comment('Country code for trends');
            $table->string('language', 5)->default('id')->comment('Output language (id/en)');
            $table->integer('total_results')->default(0)->comment('Number of results found');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();

            // Indices
            $table->index('created_by');
            $table->index('platform');
            $table->index('status');

            // Foreign key
            $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_social_searches');
    }
};
