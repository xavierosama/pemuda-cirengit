<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FinancialCategory extends Model
{
    public const TYPES = [
        'income' => 'Pemasukan',
        'expense' => 'Pengeluaran',
    ];

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (FinancialCategory $category) {
            if (blank($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? str($this->type)->headline();
    }
}
