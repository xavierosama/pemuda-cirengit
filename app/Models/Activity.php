<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Activity extends Model
{
    protected $fillable = [
        'agenda_schedule_id',
        'department_id',
        'pic_id',
        'title',
        'description',
        'activity_date',
        'start_time',
        'end_time',
        'location',
        'latitude',
        'longitude',
        'attendance_radius',
        'status',
        'change_reason',
        'attendance_enabled',
        'attendance_open_at',
        'attendance_close_at',
        'attendance_token',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'attendance_enabled' => 'boolean',
            'attendance_open_at' => 'datetime',
            'attendance_close_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function agendaSchedule(): BelongsTo
    {
        return $this->belongsTo(AgendaSchedule::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'pic_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceAvailability(?Carbon $now = null): string
    {
        if (in_array($this->status, ['holiday', 'cancelled', 'postponed'], true)) {
            return 'not_available';
        }

        if ($this->status === 'completed') {
            return 'closed';
        }

        if (! $this->attendance_enabled || ! $this->attendance_open_at || ! $this->attendance_close_at) {
            return 'not_available';
        }

        $now ??= now();

        if ($now->lt($this->attendance_open_at)) {
            return 'not_open';
        }

        if ($now->gt($this->attendance_close_at)) {
            return 'closed';
        }

        return 'open';
    }

    public function attendanceAvailabilityLabel(?Carbon $now = null): string
    {
        return match ($this->attendanceAvailability($now)) {
            'open' => 'Dibuka',
            'not_open' => 'Belum Dibuka',
            'closed' => 'Ditutup',
            default => 'Tidak Tersedia',
        };
    }

    public function attendanceAvailabilityBadgeStatus(?Carbon $now = null): string
    {
        return match ($this->attendanceAvailability($now)) {
            'open' => 'active',
            'not_open' => 'scheduled',
            'closed' => 'completed',
            default => 'inactive',
        };
    }

    public function attendanceIsOpen(?Carbon $now = null): bool
    {
        return $this->attendanceAvailability($now) === 'open';
    }
}
