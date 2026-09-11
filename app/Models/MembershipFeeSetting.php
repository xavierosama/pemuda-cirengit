<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MembershipFeeSetting extends Model
{
    protected $fillable = [
        'name',
        'default_amount',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'integer',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
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
