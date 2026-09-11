<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'secretary', 'bendahara', 'member') NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'secretary', 'member') NULL");
    }
};
