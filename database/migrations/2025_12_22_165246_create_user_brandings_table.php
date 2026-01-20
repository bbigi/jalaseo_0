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
        Schema::create('ane_user_brandings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('website_id')->nullable();
            $table->string('preset_name', 100);
            $table->unsignedBigInteger('logo_id')->nullable();
            $table->string('primary_color', 7)->default('#000000');
            $table->string('secondary_color', 7)->default('#ffffff');
            $table->string('default_font', 50)->default('Inter');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('ane_users')->onDelete('cascade');
            $table->foreign('website_id')->references('id')->on('ane_websites')->onDelete('set null');
            $table->foreign('logo_id')->references('id')->on('ane_media')->onDelete('set null');

            $table->index(['user_id', 'website_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_user_brandings');
    }
};
