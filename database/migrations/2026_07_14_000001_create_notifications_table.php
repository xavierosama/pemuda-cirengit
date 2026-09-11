<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('dedupe_key')->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('url')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['member_id', 'read_at']);
            $table->index(['type', 'created_at']);
            $table->unique(['user_id', 'dedupe_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
