<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'member_id',
        'category',
        'in_app_enabled',
        'push_enabled',
    ];

    protected function casts(): array
    {
        return [
            'in_app_enabled' => 'boolean',
            'push_enabled' => 'boolean',
        ];
    }

    public const CATEGORIES = [
        'activity_reminder' => [
            'label' => 'Reminder kegiatan',
            'description' => 'Info kegiatan baru atau kegiatan yang akan dimulai.',
            'in_app_enabled' => true,
            'push_enabled' => true,
        ],
        'attendance_opened' => [
            'label' => 'Presensi dibuka',
            'description' => 'Pemberitahuan saat presensi kegiatan sudah bisa diisi.',
            'in_app_enabled' => true,
            'push_enabled' => true,
        ],
        'attendance_closing_soon' => [
            'label' => 'Presensi hampir ditutup',
            'description' => 'Pengingat saat waktu presensi akan segera selesai.',
            'in_app_enabled' => true,
            'push_enabled' => true,
        ],
        'attendance_not_submitted' => [
            'label' => 'Belum melakukan presensi',
            'description' => 'Pengingat jika Anda belum mengisi presensi pada kegiatan aktif.',
            'in_app_enabled' => true,
            'push_enabled' => true,
        ],
        'activity_updated' => [
            'label' => 'Perubahan jadwal/lokasi',
            'description' => 'Info perubahan waktu, tempat, atau detail penting kegiatan.',
            'in_app_enabled' => true,
            'push_enabled' => true,
        ],
        'admin_announcement' => [
            'label' => 'Pengumuman pengurus',
            'description' => 'Informasi penting yang dikirim langsung oleh pengurus.',
            'in_app_enabled' => true,
            'push_enabled' => true,
        ],
        'attendance_status' => [
            'label' => 'Status presensi',
            'description' => 'Info verifikasi, persetujuan, atau penolakan presensi Anda.',
            'in_app_enabled' => true,
            'push_enabled' => true,
        ],
        'profile_reminder' => [
            'label' => 'Reminder profil',
            'description' => 'Pengingat untuk melengkapi data profil anggota.',
            'in_app_enabled' => true,
            'push_enabled' => false,
        ],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public static function categories(): array
    {
        return self::CATEGORIES;
    }

    public static function categoryKeys(): array
    {
        return array_keys(self::CATEGORIES);
    }

    public static function typeToCategory(string $type): string
    {
        return match ($type) {
            'activity_created',
            'activity_info',
            'activity_reminder' => 'activity_reminder',
            'attendance_open',
            'attendance_opened' => 'attendance_opened',
            'attendance_closing',
            'attendance_closing_soon' => 'attendance_closing_soon',
            'attendance_pending',
            'attendance_not_submitted' => 'attendance_not_submitted',
            'activity_changed',
            'schedule_change',
            'activity_updated' => 'activity_updated',
            'attendance_verification_pending',
            'attendance_pending_verification',
            'attendance_verified',
            'attendance_rejected' => 'attendance_status',
            'profile_update',
            'profile_incomplete' => 'profile_reminder',
            'admin_announcement',
            'announcement',
            'organization_info',
            'urgent',
            'general' => 'admin_announcement',
            'attendance_instruction',
            'attendance_warning' => 'attendance_status',
            default => array_key_exists($type, self::CATEGORIES) ? $type : 'admin_announcement',
        };
    }

    public static function typesForCategory(string $category): array
    {
        return match ($category) {
            'activity_reminder' => ['activity_created', 'activity_reminder'],
            'attendance_opened' => ['attendance_open', 'attendance_opened'],
            'attendance_closing_soon' => ['attendance_closing', 'attendance_closing_soon'],
            'attendance_not_submitted' => ['attendance_pending', 'attendance_not_submitted'],
            'activity_updated' => ['activity_changed', 'schedule_change', 'activity_updated'],
            'admin_announcement' => ['announcement', 'admin_announcement', 'organization_info', 'urgent', 'general'],
            'attendance_status' => ['attendance_instruction', 'attendance_warning', 'attendance_verification_pending', 'attendance_pending_verification', 'attendance_verified', 'attendance_rejected'],
            'profile_reminder' => ['profile_update', 'profile_incomplete'],
            default => [$category],
        };
    }

    public static function defaultsFor(string $category): array
    {
        $definition = self::CATEGORIES[$category] ?? null;

        return [
            'in_app_enabled' => (bool) ($definition['in_app_enabled'] ?? true),
            'push_enabled' => (bool) ($definition['push_enabled'] ?? true),
        ];
    }

    public static function resolvedForUser(User $user): array
    {
        $saved = self::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('category');

        return collect(self::CATEGORIES)
            ->mapWithKeys(fn (array $definition, string $category) => [
                $category => [
                    ...$definition,
                    'category' => $category,
                    'in_app_enabled' => $saved[$category]->in_app_enabled ?? $definition['in_app_enabled'],
                    'push_enabled' => $saved[$category]->push_enabled ?? $definition['push_enabled'],
                ],
            ])
            ->all();
    }

    public static function allowsInApp(User $user, string $category): bool
    {
        return self::allows($user, $category, 'in_app_enabled');
    }

    public static function allowsPush(User $user, string $category): bool
    {
        return self::allows($user, $category, 'push_enabled');
    }

    public static function updateForUser(User $user, array $preferences): void
    {
        $memberId = $user->member_id ?: $user->member?->id;

        foreach (self::categoryKeys() as $category) {
            $input = $preferences[$category] ?? [];
            $defaults = self::defaultsFor($category);

            self::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category' => $category,
                ],
                [
                    'member_id' => $memberId,
                    'in_app_enabled' => (bool) ($input['in_app_enabled'] ?? $defaults['in_app_enabled']),
                    'push_enabled' => (bool) ($input['push_enabled'] ?? $defaults['push_enabled']),
                ]
            );
        }
    }

    public function allowsInAppChannel(): bool
    {
        return $this->in_app_enabled;
    }

    public function allowsPushChannel(): bool
    {
        return $this->push_enabled;
    }

    private static function allows(User $user, string $category, string $column): bool
    {
        $category = array_key_exists($category, self::CATEGORIES) ? $category : self::typeToCategory($category);

        $preference = self::query()
            ->where('user_id', $user->id)
            ->where('category', $category)
            ->first();

        if ($preference) {
            return (bool) $preference->{$column};
        }

        return (bool) self::defaultsFor($category)[$column];
    }
}
