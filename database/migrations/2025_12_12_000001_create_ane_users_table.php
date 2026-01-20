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
        Schema::create('ane_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['user', 'super_admin'])->default('user');
            $table->rememberToken();
            
            // SaaS subscription fields
            $table->string('subscription_plan')->default('free');
            $table->enum('subscription_status', ['active', 'inactive', 'cancelled', 'expired'])->default('inactive');
            $table->timestamp('subscription_ends_at')->nullable();
            
            // API quota management
            $table->unsignedInteger('api_quota_used')->default(0);
            $table->unsignedInteger('api_quota_limit')->default(1000);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indices
            $table->index('email');
            $table->index('role');
            $table->index('subscription_status');
            $table->index('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_users');
    }
};
