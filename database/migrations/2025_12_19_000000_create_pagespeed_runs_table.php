<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ane_pagespeed_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('ane_websites')->cascadeOnDelete();
            $table->string('url');
            $table->enum('strategy', ['mobile', 'desktop'])->default('mobile');
            $table->enum('status', ['queued', 'running', 'success', 'error'])->default('queued');
            $table->tinyInteger('progress')->default(0);
            $table->string('error_message')->nullable();
            $table->string('lighthouse_version')->nullable();
            $table->dateTime('fetch_time')->nullable();
            $table->json('scores')->nullable();
            $table->json('lab_metrics')->nullable();
            $table->json('field_metrics')->nullable();
            $table->json('opportunities')->nullable();
            $table->json('diagnostics')->nullable();
            $table->json('resources_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ane_pagespeed_runs');
    }
};
