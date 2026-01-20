<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ane_websites', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('url', 255);
            $table->string('username', 100);
            $table->string('app_password', 255);
            $table->enum('category', [
                'business_industrial',
                'shopping_ecommerce',
                'technology_software',
                'education_content',
                'news_media',
                'health_beauty',
                'food_beverage',
                'travel_hospitality',
                'finance_investment',
                'real_estate',
                'automotive',
                'entertainment',
                'personal_brand',
                'other'
            ])->default('other');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['created_by', 'status']);
            $table->foreign('created_by')->references('id')->on('ane_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ane_websites');
    }
};
