<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function agendaSchedules(): HasMany
    {
        return $this->hasMany(AgendaSchedule::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
