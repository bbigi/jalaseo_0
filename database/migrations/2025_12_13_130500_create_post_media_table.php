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
        Schema::create('ane_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('ane_posts')->onDelete('cascade');
            $table->foreignId('media_id')->constrained('ane_media')->onDelete('cascade');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('post_id');
            $table->index('media_id');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_post_media');
    }
};
