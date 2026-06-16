<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE agenda_schedules MODIFY schedule_type ENUM('once', 'daily', 'incidental', 'weekly', 'monthly', 'yearly') NOT NULL");
        }

        DB::table('agenda_schedules')
            ->whereIn('schedule_type', ['once', 'daily'])
            ->update(['schedule_type' => 'incidental']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE agenda_schedules MODIFY schedule_type ENUM('incidental', 'weekly', 'monthly', 'yearly') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE agenda_schedules MODIFY schedule_type ENUM('once', 'daily', 'incidental', 'weekly', 'monthly', 'yearly') NOT NULL");
        }

        DB::table('agenda_schedules')
            ->where('schedule_type', 'incidental')
            ->update(['schedule_type' => 'once']);

        DB::table('agenda_schedules')
            ->where('schedule_type', 'yearly')
            ->update(['schedule_type' => 'monthly']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE agenda_schedules MODIFY schedule_type ENUM('once', 'daily', 'weekly', 'monthly') NOT NULL");
        }
    }
};
