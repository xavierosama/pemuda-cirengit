<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category');
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'category']);
            $table->index(['member_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
