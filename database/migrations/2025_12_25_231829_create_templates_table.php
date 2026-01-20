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
        Schema::create('ane_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('category', ['quote', 'article', 'news', 'promo', 'custom'])->default('custom');
            $table->text('canvas_json')->comment('Konva Stage JSON with placeholders');
            $table->string('thumbnail_path', 255)->nullable();
            $table->integer('canvas_width')->default(1080);
            $table->integer('canvas_height')->default(1080);
            $table->boolean('is_public')->default(0)->comment('0 = user template, 1 = platform template');
            $table->boolean('is_default')->default(0)->comment('Default template for category');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['is_public', 'category']);
            $table->index('created_by');
            $table->index(['is_default', 'category']);

            // Foreign key
            $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_templates');
    }
};
