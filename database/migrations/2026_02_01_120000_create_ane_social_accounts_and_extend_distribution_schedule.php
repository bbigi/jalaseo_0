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
        Schema::create('ane_social_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider', 50); // meta_facebook, meta_instagram, meta_threads, x, tiktok, youtube, linkedin
            $table->string('account_id', 191)->nullable(); // user/page/business id from provider
            $table->string('page_id', 191)->nullable(); // fb page id
            $table->string('ig_business_account_id', 191)->nullable(); // ig biz id if available
            $table->string('username', 191)->nullable();
            $table->text('access_token_enc')->nullable();
            $table->text('refresh_token_enc')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->enum('status', ['active', 'revoked', 'expired'])->default('active');
            $table->json('metadata')->nullable(); // store misc provider info (permissions, avatar, etc)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('ane_users')->onDelete('cascade');
            $table->index(['user_id', 'provider']);
            $table->index(['provider', 'status']);
            $table->index('expires_at');
            $table->unique(['user_id', 'provider', 'account_id']);
        });

        Schema::table('ane_distribution_schedule', function (Blueprint $table) {
            $table->unsignedSmallInteger('attempt_count')->default(0)->after('publish_status');
            $table->timestamp('queued_at')->nullable()->after('attempt_count');
            $table->text('last_error')->nullable()->after('queued_at');
            $table->json('publish_payload')->nullable()->after('last_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ane_social_accounts');

        Schema::table('ane_distribution_schedule', function (Blueprint $table) {
            $table->dropColumn(['attempt_count', 'queued_at', 'last_error', 'publish_payload']);
        });
    }
};
