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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pic_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('title');
            $table->date('activity_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('attendance_radius')->default(100);
            $table->enum('status', ['scheduled', 'completed', 'holiday', 'postponed', 'relocated', 'cancelled'])->default('scheduled');
            $table->text('change_reason')->nullable();
            $table->boolean('attendance_enabled')->default(false);
            $table->dateTime('attendance_open_at')->nullable();
            $table->dateTime('attendance_close_at')->nullable();
            $table->string('attendance_token')->nullable()->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
