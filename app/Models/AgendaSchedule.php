<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgendaSchedule extends Model
{
    protected $fillable = [
        'department_id',
        'pic_id',
        'title',
        'description',
        'schedule_type',
        'day_of_week',
        'day_of_month',
        'specific_date',
        'start_time',
        'end_time',
        'default_location',
        'default_latitude',
        'default_longitude',
        'default_radius',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'specific_date' => 'date',
            'is_active' => 'boolean',
            'default_latitude' => 'decimal:7',
            'default_longitude' => 'decimal:7',
        ];
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

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function weeklyTopics(): HasMany
    {
        return $this->hasMany(AgendaWeeklyTopic::class);
    }
}
