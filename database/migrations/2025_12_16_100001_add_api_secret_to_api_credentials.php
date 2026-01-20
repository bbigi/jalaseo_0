<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ane_api_credentials', function (Blueprint $table) {
            $table->text('api_secret')->nullable()->after('api_key')->comment('Encrypted secondary credential (password, secret key)');
        });
    }

    public function down(): void
    {
        Schema::table('ane_api_credentials', function (Blueprint $table) {
            $table->dropColumn('api_secret');
        });
    }
};
