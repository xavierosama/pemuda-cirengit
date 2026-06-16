<?php

namespace App\Services;

use App\Models\Activity;
use App\Support\SystemSettings;
use Illuminate\Support\Carbon;

class ActivityAttendanceScheduleService
{
    public function __construct(private readonly SystemSettings $settings)
    {
    }

    public function applyToData(array &$data): void
    {
        $data['attendance_enabled'] = $this->isAttendanceScheduledStatus($data['status'] ?? null);
        $times = $this->times(
            $data['activity_date'] ?? null,
            $data['start_time'] ?? null,
            $data['end_time'] ?? null
        );

        $data['attendance_open_at'] = $times['open_at'];
        $data['attendance_close_at'] = $times['close_at'];
    }

    public function times(?string $activityDate, mixed $startTime, mixed $endTime): array
    {
        $timezone = config('app.timezone');
        $date = $activityDate ? Carbon::parse($activityDate, $timezone)->format('Y-m-d') : null;

        return [
            'open_at' => $date && $startTime
                ? Carbon::createFromFormat('Y-m-d H:i', "{$date} ".$this->formatTime($startTime), $timezone)
                    ->subMinutes($this->openMinutesBefore())
                : null,
            'close_at' => $date && $endTime
                ? Carbon::createFromFormat('Y-m-d H:i', "{$date} ".$this->formatTime($endTime), $timezone)
                : null,
        ];
    }

    public function statusKey(Activity $activity, ?Carbon $now = null): string
    {
        if (in_array($activity->status, ['holiday', 'cancelled', 'postponed'], true)) {
            return 'not_available';
        }

        if ($activity->status === 'completed') {
            return 'closed';
        }

        $openAt = $this->openAt($activity);
        $closeAt = $this->closeAt($activity);

        if (! $this->isAttendanceScheduledStatus($activity->status) || ! $openAt || ! $closeAt) {
            return 'not_available';
        }

        $now ??= Carbon::now(config('app.timezone'));
        $now = $now->copy()->timezone(config('app.timezone'));

        if ($now->lt($openAt)) {
            return 'not_open';
        }

        if ($now->gt($closeAt)) {
            return 'closed';
        }

        return 'open';
    }

    public function statusLabel(Activity $activity, ?Carbon $now = null): string
    {
        return match ($this->statusKey($activity, $now)) {
            'open' => 'Dibuka',
            'not_open' => 'Belum Dibuka',
            'closed' => 'Ditutup',
            default => 'Tidak Tersedia',
        };
    }

    public function openAt(Activity $activity): ?Carbon
    {
        if ($activity->attendance_open_at) {
            return $activity->attendance_open_at->copy()->timezone(config('app.timezone'));
        }

        if (! $this->isAttendanceScheduledStatus($activity->status)) {
            return null;
        }

        return $this->times(
            $activity->activity_date?->format('Y-m-d'),
            $activity->start_time,
            $activity->end_time
        )['open_at'];
    }

    public function closeAt(Activity $activity): ?Carbon
    {
        if ($activity->attendance_close_at) {
            return $activity->attendance_close_at->copy()->timezone(config('app.timezone'));
        }

        if (! $this->isAttendanceScheduledStatus($activity->status)) {
            return null;
        }

        return $this->times(
            $activity->activity_date?->format('Y-m-d'),
            $activity->start_time,
            $activity->end_time
        )['close_at'];
    }

    public function isAttendanceScheduledStatus(?string $status): bool
    {
        return in_array($status, ['scheduled', 'relocated', 'completed'], true);
    }

    public function openMinutesBefore(): int
    {
        return $this->settings->attendanceDefaults()['open_minutes_before'] ?? 30;
    }

    private function formatTime(mixed $time): string
    {
        if ($time instanceof Carbon) {
            return $time->format('H:i');
        }

        return substr((string) $time, 0, 5);
    }
}
