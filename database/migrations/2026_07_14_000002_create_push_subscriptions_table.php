<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('endpoint', 500);
            $table->text('public_key')->nullable();
            $table->text('auth_token')->nullable();
            $table->string('content_encoding')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_name')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique('endpoint', 'push_subscriptions_endpoint_unique');
            $table->index(['user_id', 'revoked_at']);
            $table->index(['member_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
