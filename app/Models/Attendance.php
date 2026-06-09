<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'activity_id',
        'member_id',
        'status',
        'attendance_method',
        'checked_in_at',
        'latitude',
        'longitude',
        'distance_from_activity',
        'location_accuracy',
        'verification_status',
        'verified_by',
        'verified_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'verified_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'distance_from_activity' => 'decimal:2',
            'location_accuracy' => 'decimal:2',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
