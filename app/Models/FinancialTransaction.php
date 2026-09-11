<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'income' => 'Pemasukan',
        'expense' => 'Pengeluaran',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Tunai',
        'transfer' => 'Transfer',
        'other' => 'Lainnya',
    ];

    protected $fillable = [
        'type',
        'financial_category_id',
        'amount',
        'transaction_date',
        'title',
        'description',
        'payment_method',
        'reference_no',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'transaction_date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'financial_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? str($this->type)->headline();
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? '-';
    }
}
