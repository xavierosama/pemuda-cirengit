<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('announcement');
            $table->string('target_type')->default('active_members');
            $table->json('target_payload')->nullable();
            $table->string('channel')->default('in_app');
            $table->string('url')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('target_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('push_attempted_count')->default(0);
            $table->unsignedInteger('push_sent_count')->default(0);
            $table->unsignedInteger('push_failed_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['channel', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
