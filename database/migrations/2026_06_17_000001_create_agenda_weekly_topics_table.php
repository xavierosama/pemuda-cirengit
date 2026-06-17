<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_weekly_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_schedule_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week_number');
            $table->string('topic')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['agenda_schedule_id', 'week_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_weekly_topics');
    }
};
