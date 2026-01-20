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
        Schema::create('ane_gsc_sync_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')->constrained('ane_gsc_connections')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('job_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('progress')->default(0);
            $table->string('current_step')->nullable();
            $table->integer('keywords_synced')->default(0);
            $table->integer('pages_synced')->default(0);
            $table->text('error_message')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('job_status');
            $table->index(['connection_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_gsc_sync_jobs');
    }
};
