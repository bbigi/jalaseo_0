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
        Schema::create('ane_api_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('category'); // 'article_generation', 'image_generation', etc.
            $table->string('provider'); // 'gemini', 'chatgpt', 'deepseek', 'flux', etc.
            $table->text('api_key'); // Encrypted API key
            $table->string('display_name')->nullable(); // e.g., "Gemini API"
            $table->text('description')->nullable(); // Notes about the credential
            $table->boolean('is_active')->default(true); // Enable/disable without deleting
            $table->timestamp('last_verified_at')->nullable(); // Last successful API call
            $table->timestamps();
            $table->softDeletes();

            // Composite unique constraint: one credential per provider per category
            $table->unique(['category', 'provider']);
            
            // Indices for faster queries
            $table->index('category');
            $table->index('provider');
            $table->index('is_active');
            $table->index('user_id');
            
            // Foreign key (optional, for future user association)
            $table->foreign('user_id')->references('id')->on('ane_users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_api_credentials');
    }
};
