<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    public const TYPES = [
        'announcement' => 'Pengumuman',
        'activity_info' => 'Informasi Kegiatan',
        'activity_reminder' => 'Reminder Kegiatan',
        'attendance_instruction' => 'Instruksi Presensi',
        'schedule_change' => 'Perubahan Jadwal/Lokasi',
        'attendance_warning' => 'Peringatan Presensi',
        'profile_update' => 'Update Profil',
        'organization_info' => 'Informasi Organisasi',
        'urgent' => 'Penting/Mendesak',
        'general' => 'Umum',
    ];

    public const TARGET_TYPES = [
        'all_active_members' => 'Semua Anggota Aktif',
        'all_members' => 'Semua Anggota',
        'active_members' => 'Semua Member Aktif',
        'by_bidang' => 'Berdasarkan Bidang',
        'by_jabatan' => 'Berdasarkan Jabatan',
        'selected_members' => 'Member Tertentu',
    ];

    public const CHANNELS = [
        'in_app' => 'In-App',
        'push' => 'Push',
        'both' => 'In-App + Push',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Terkirim',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'title',
        'message',
        'type',
        'target_type',
        'target_payload',
        'channel',
        'url',
        'status',
        'sent_at',
        'created_by',
        'target_count',
        'delivered_count',
        'skipped_count',
        'push_attempted_count',
        'push_sent_count',
        'push_failed_count',
    ];

    protected function casts(): array
    {
        return [
            'target_payload' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function canBeSent(): bool
    {
        return $this->isDraft();
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? str($this->type)->headline()->toString();
    }

    public function targetLabel(): string
    {
        return self::TARGET_TYPES[$this->target_type] ?? str($this->target_type)->headline()->toString();
    }

    public function channelLabel(): string
    {
        return self::CHANNELS[$this->channel] ?? str($this->channel)->headline()->toString();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? str($this->status)->headline()->toString();
    }
}
