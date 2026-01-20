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
        Schema::table('ane_media', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->unsignedBigInteger('post_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ane_media', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id')->nullable(false)->change();
            $table->foreign('post_id')->references('id')->on('ane_posts')->onDelete('cascade');
        });
    }
};
