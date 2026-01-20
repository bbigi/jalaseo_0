<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('ane_insights', function (Blueprint $table) {
            $table->string('source_type', 32)->change();
        });
    }

    public function down()
    {
        Schema::table('ane_insights', function (Blueprint $table) {
            $table->string('source_type', 10)->change(); // Ubah ke ukuran sebelumnya jika perlu
        });
    }
};
