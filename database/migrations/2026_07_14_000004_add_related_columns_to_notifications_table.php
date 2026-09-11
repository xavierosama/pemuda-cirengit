<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('related_type')->nullable()->after('type');
            $table->unsignedBigInteger('related_id')->nullable()->after('related_type');

            $table->index(['user_id', 'type', 'related_type', 'related_id'], 'notifications_user_related_index');
            $table->index(['member_id', 'type', 'related_type', 'related_id'], 'notifications_member_related_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_related_index');
            $table->dropIndex('notifications_member_related_index');
            $table->dropColumn(['related_type', 'related_id']);
        });
    }
};
