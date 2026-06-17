<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    protected $fillable = [
        'department_id',
        'position_id',
        'full_name',
        'npa',
        'phone',
        'email',
        'address',
        'profile_photo',
        'joined_at',
        'birth_date',
        'member_status',
        'inactive_reason',
        'inactive_at',
        'status_notes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'birth_date' => 'date',
            'inactive_at' => 'date',
        ];
    }

    public const INACTIVE_REASONS = [
        'rarely_active' => 'Jarang aktif mengikuti kegiatan',
        'resigned' => 'Mengundurkan diri',
        'age_limit' => 'Melebihi batas usia Pemuda',
        'transferred_to_persis' => 'Dimutasikan ke Persis',
        'moved_domicile' => 'Pindah domisili',
        'other' => 'Lainnya',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function agendaSchedulesAsPic(): HasMany
    {
        return $this->hasMany(AgendaSchedule::class, 'pic_id');
    }

    public function activitiesAsPic(): HasMany
    {
        return $this->hasMany(Activity::class, 'pic_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function displayStatusKey(): string
    {
        return $this->member_status === 'active' ? 'active' : 'inactive';
    }

    public function displayStatusLabel(): string
    {
        return $this->displayStatusKey() === 'active' ? 'Aktif' : 'Tidak Aktif';
    }
}
