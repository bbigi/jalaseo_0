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
        Schema::create('ane_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id');
            $table->integer('wp_tag_id'); // WordPress tag ID
            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            // Foreign key
            $table->foreign('website_id')->references('id')->on('ane_websites')->onDelete('cascade');

            // Unique constraint: one tag per website
            $table->unique(['website_id', 'wp_tag_id']);

            // Index for fast lookup
            $table->index(['website_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_tags');
    }
};
