<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'member_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
        'user_agent',
        'device_name',
        'last_used_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    public function scopeRevoked(Builder $query): Builder
    {
        return $query->whereNotNull('revoked_at');
    }

    public function revoke(): void
    {
        if (! $this->revoked_at) {
            $this->forceFill(['revoked_at' => now()])->save();
        }
    }
}
