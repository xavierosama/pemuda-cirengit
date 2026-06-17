<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SystemSettings
{
    public const DEFAULT_WHATSAPP_GROUP_REMINDER_TEMPLATE = "Assalamu’alaikum warahmatullahi wabarakatuh.\n\nIkhwatu iman sekalian, kami ingatkan kembali agenda berikut:\n\n📌 Kegiatan: {nama_kegiatan}\n📝 Topik: {topic}\n📅 Hari/Tanggal: {hari_tanggal}\n🕗 Waktu: {jam_mulai} - {jam_selesai} WIB\n📍 Tempat: {lokasi}\n\nSilakan melakukan presensi melalui link berikut:\n{link_presensi}\n\nJazaakumullahu khairan.\nWassalamu’alaikum warahmatullahi wabarakatuh.";

    public const DEFAULTS = [
        'app_name' => 'Pemuda Cirengit',
        'organization_name' => 'Pemuda Persis Cirengit',
        'app_logo' => null,
        'login_logo' => null,
        'favicon' => null,
        'theme_mode' => 'light',
        'default_attendance_radius' => '100',
        'default_attendance_open_minutes_before' => '30',
        'default_attendance_close_minutes_after' => '30',
        'default_location_accuracy_tolerance' => '50',
        'whatsapp_group_reminder_template' => self::DEFAULT_WHATSAPP_GROUP_REMINDER_TEMPLATE,
    ];

    public function all(): array
    {
        $settings = self::DEFAULTS;

        try {
            if (! Schema::hasTable('settings')) {
                return $settings;
            }

            Setting::query()
                ->whereIn('key', array_keys(self::DEFAULTS))
                ->pluck('value', 'key')
                ->each(function ($value, $key) use (&$settings) {
                    if ($value !== null && $value !== '') {
                        $settings[$key] = $value;
                    }
                });
        } catch (Throwable) {
            return $settings;
        }

        return $settings;
    }

    public function get(string $key, ?string $fallback = null): ?string
    {
        $settings = $this->all();

        return $settings[$key] ?? $fallback;
    }

    public function assetUrl(string $key): ?string
    {
        $path = $this->get($key);

        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function themeMode(): string
    {
        $themeMode = $this->get('theme_mode', 'light');

        return in_array($themeMode, ['light', 'dark', 'system'], true) ? $themeMode : 'light';
    }

    public function attendanceDefaults(): array
    {
        $settings = $this->all();

        return [
            'radius' => max(1, (int) $settings['default_attendance_radius']),
            'open_minutes_before' => max(0, (int) $settings['default_attendance_open_minutes_before']),
            'close_minutes_after' => max(0, (int) $settings['default_attendance_close_minutes_after']),
            'location_accuracy_tolerance' => max(0, (int) $settings['default_location_accuracy_tolerance']),
        ];
    }

    public function whatsappGroupReminderTemplate(): string
    {
        return $this->get('whatsapp_group_reminder_template', self::DEFAULT_WHATSAPP_GROUP_REMINDER_TEMPLATE)
            ?: self::DEFAULT_WHATSAPP_GROUP_REMINDER_TEMPLATE;
    }

    public function set(string $key, ?string $value, ?string $type = null): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type],
        );
    }
}
