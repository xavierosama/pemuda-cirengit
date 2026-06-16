<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use App\Services\ActivityAttendanceScheduleService;

class Activity extends Model
{
    protected $fillable = [
        'agenda_schedule_id',
        'department_id',
        'pic_id',
        'title',
        'topic',
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
        return $this->getAttendanceStatusKey($now);
    }

    public function attendanceAvailabilityLabel(?Carbon $now = null): string
    {
        return $this->getAttendanceStatusLabel($now);
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
        return $this->isAttendanceOpen($now);
    }

    public function getAttendanceStatusKey(?Carbon $now = null): string
    {
        return app(ActivityAttendanceScheduleService::class)->statusKey($this, $now);
    }

    public function getAttendanceStatusLabel(?Carbon $now = null): string
    {
        return app(ActivityAttendanceScheduleService::class)->statusLabel($this, $now);
    }

    public function isAttendanceOpen(?Carbon $now = null): bool
    {
        return $this->getAttendanceStatusKey($now) === 'open';
    }

    public function effectiveAttendanceOpenAt(): ?Carbon
    {
        return app(ActivityAttendanceScheduleService::class)->openAt($this);
    }

    public function effectiveAttendanceCloseAt(): ?Carbon
    {
        return app(ActivityAttendanceScheduleService::class)->closeAt($this);
    }
}
