<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ane_keyword_traces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('keyword_id');
            $table->string('engine')->default('google');
            $table->integer('position')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('traced_at');
            $table->timestamps();

            $table->index(['keyword_id', 'engine', 'traced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ane_keyword_traces');
    }
};
