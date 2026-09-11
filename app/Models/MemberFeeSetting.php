<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberFeeSetting extends Model
{
    protected $fillable = [
        'member_id',
        'amount',
        'effective_from',
        'effective_to',
        'is_active',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeActiveFor(Builder $query, mixed $date): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date);
            });
    }
}
