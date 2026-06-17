<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('joined_at');
            $table->string('inactive_reason')->nullable()->after('member_status');
            $table->date('inactive_at')->nullable()->after('inactive_reason');
            $table->text('status_notes')->nullable()->after('inactive_at');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'inactive_reason',
                'inactive_at',
                'status_notes',
            ]);
        });
    }
};
