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
        Schema::create('ane_general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Setting key (e.g., default_text_ai, default_image_ai)');
            $table->json('value')->comment('Setting value (JSON for arrays/objects)');
            $table->text('description')->nullable()->comment('Human-readable description');
            $table->timestamps();

            // Indexes
            $table->index('key');
        });

        // Insert default settings
        DB::table('ane_general_settings')->insert([
            [
                'key' => 'default_text_ai',
                'value' => json_encode(['gemini', 'deepseek']),
                'description' => 'Default AI providers for text generation (fallback chain)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_image_ai',
                'value' => json_encode(['flux']),
                'description' => 'Default AI providers for image generation (fallback chain)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_general_settings');
    }
};
