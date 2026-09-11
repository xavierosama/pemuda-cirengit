<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberFeeRecord extends Model
{
    protected $table = 'member_fee_records';

    public const STATUSES = [
        'unpaid' => 'Belum Bayar',
        'paid' => 'Lunas',
        'waived' => 'Dibebaskan',
    ];

    protected $fillable = [
        'member_id',
        'year',
        'month',
        'amount',
        'status',
        'paid_at',
        'note',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'amount' => 'integer',
            'paid_at' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopePeriod(Builder $query, int $year, int $month): Builder
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? str($this->status)->headline();
    }

    public function displayStatus(): string
    {
        if ($this->status === 'unpaid' && $this->isOverdue()) {
            return 'overdue';
        }

        return $this->status;
    }

    public function displayStatusLabel(): string
    {
        return $this->displayStatus() === 'overdue'
            ? 'Menunggak'
            : $this->statusLabel();
    }

    public function isOverdue(): bool
    {
        if ($this->status !== 'unpaid') {
            return false;
        }

        return now()->startOfMonth()->greaterThan(
            now()->setDate($this->year, $this->month, 1)->startOfMonth()
        );
    }
}
