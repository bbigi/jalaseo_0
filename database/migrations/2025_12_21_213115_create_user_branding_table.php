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
        Schema::create('ane_user_branding', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('website_id')->nullable();
            $table->unsignedBigInteger('logo_media_id')->nullable();
            $table->string('primary_color', 7)->default('#dc2626');
            $table->string('secondary_color', 7)->default('#2563eb');
            $table->string('default_font', 50)->default('Inter');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('ane_users')->onDelete('cascade');
            $table->foreign('website_id')->references('id')->on('ane_websites')->onDelete('cascade');
            $table->foreign('logo_media_id')->references('id')->on('ane_media')->onDelete('set null');
            $table->unique(['user_id', 'website_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_user_branding');
    }
};
