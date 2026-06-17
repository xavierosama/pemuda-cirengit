<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaWeeklyTopic extends Model
{
    protected $fillable = [
        'agenda_schedule_id',
        'week_number',
        'topic',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function agendaSchedule(): BelongsTo
    {
        return $this->belongsTo(AgendaSchedule::class);
    }
}
