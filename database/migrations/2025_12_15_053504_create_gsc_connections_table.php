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
        Schema::create('ane_gsc_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('ane_websites')->onDelete('cascade');
            $table->string('property_url', 500); // e.g., "sc-domain:example.com"
            $table->text('access_token'); // ENCRYPTED by model
            $table->text('refresh_token')->nullable(); // ENCRYPTED by model
            $table->dateTime('token_expires_at');
            $table->boolean('verified')->default(false);
            $table->dateTime('last_sync_at')->nullable();
            $table->enum('sync_status', ['pending', 'syncing', 'completed', 'failed'])->default('pending');
            $table->text('sync_error')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique('website_id', 'uk_website');
            $table->index('sync_status');
            $table->index(['verified', 'last_sync_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_gsc_connections');
    }
};
