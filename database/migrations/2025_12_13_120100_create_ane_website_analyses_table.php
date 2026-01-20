<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ane_website_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id');
            $table->string('type'); // structure|seo|performance
            $table->string('status')->default('pending'); // pending|running|done|failed
            $table->json('result_json')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ane_website_analyses');
    }
};
