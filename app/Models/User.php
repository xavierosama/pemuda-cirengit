<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'member_id',
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function createdAgendaSchedules(): HasMany
    {
        return $this->hasMany(AgendaSchedule::class, 'created_by');
    }

    public function createdActivities(): HasMany
    {
        return $this->hasMany(Activity::class, 'created_by');
    }

    public function createdAttendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'created_by');
    }

    public function verifiedAttendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'verified_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'created_by');
    }

    public function recordedMemberFees(): HasMany
    {
        return $this->hasMany(MemberFeeRecord::class, 'recorded_by');
    }

    public function canManageFinance(): bool
    {
        return in_array($this->role, ['admin', 'bendahara'], true);
    }
}
