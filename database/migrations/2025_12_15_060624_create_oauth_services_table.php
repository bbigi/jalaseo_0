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
        Schema::create('ane_oauth_services', function (Blueprint $table) {
            $table->id();
            $table->string('service_name', 50); // google, facebook, twitter
            $table->string('display_name', 100); // "Google OAuth (GSC & GA4)"
            $table->text('client_id'); // ENCRYPTED
            $table->text('client_secret'); // ENCRYPTED
            $table->string('redirect_uri', 500)->nullable();
            $table->text('scopes')->nullable(); // JSON array of scopes
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable(); // Admin notes
            $table->timestamps();

            // Unique constraint
            $table->unique('service_name', 'uk_service_name');

            // Indexes
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_oauth_services');
    }
};
