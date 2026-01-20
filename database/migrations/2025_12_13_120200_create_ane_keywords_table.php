<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ane_keywords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id');
            $table->string('keyword');
            $table->string('source')->default('analysis'); // analysis|manual|import
            $table->integer('volume')->nullable();
            $table->integer('difficulty')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['website_id', 'status']);
            $table->unique(['website_id', 'keyword']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ane_keywords');
    }
};
