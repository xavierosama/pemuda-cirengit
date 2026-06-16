<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Activity;
use App\Services\ActivityAttendanceScheduleService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('activities:backfill-attendance-schedule {--force : Hitung ulang jadwal presensi meskipun kolom waktu sudah terisi}', function (ActivityAttendanceScheduleService $scheduleService) {
    $query = Activity::query()
        ->whereIn('status', ['scheduled', 'relocated', 'completed'])
        ->whereNotNull('activity_date')
        ->whereNotNull('start_time')
        ->whereNotNull('end_time');

    if (! $this->option('force')) {
        $query->where(function ($query) {
            $query
                ->where('attendance_enabled', false)
                ->orWhereNull('attendance_enabled')
                ->orWhereNull('attendance_open_at')
                ->orWhereNull('attendance_close_at');
        });
    }

    $updated = 0;
    $skipped = 0;

    $query->lazyById()->each(function (Activity $activity) use ($scheduleService, &$updated, &$skipped) {
        $data = [
            'activity_date' => $activity->activity_date?->format('Y-m-d'),
            'start_time' => $activity->start_time ? substr($activity->start_time, 0, 5) : null,
            'end_time' => $activity->end_time ? substr($activity->end_time, 0, 5) : null,
            'status' => $activity->status,
        ];

        $scheduleService->applyToData($data);

        if (! $data['attendance_open_at'] || ! $data['attendance_close_at']) {
            $skipped++;

            return;
        }

        $activity->forceFill([
            'attendance_enabled' => $data['attendance_enabled'],
            'attendance_open_at' => $data['attendance_open_at'],
            'attendance_close_at' => $data['attendance_close_at'],
        ])->save();

        $updated++;
    });

    $this->info("Backfill jadwal presensi selesai. {$updated} kegiatan diperbarui, {$skipped} kegiatan dilewati.");
})->purpose('Backfill jadwal presensi otomatis untuk Kegiatan Aktual lama');
