<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_user_can_view_system_settings_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Pengaturan Sistem')
            ->assertSee('Nama Aplikasi')
            ->assertSee('Mode Tema')
            ->assertSee('Logo Halaman Login')
            ->assertSee('Default Presensi')
            ->assertSee('Default Radius Presensi')
            ->assertSee('Template Pesan WhatsApp Grup')
            ->assertSee('{nama_kegiatan}')
            ->assertSee('Reset ke Template Default');
    }

    public function test_member_cannot_access_system_settings_page(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get(route('settings.edit'))
            ->assertRedirect(route('member.home'));
    }

    public function test_internal_user_can_update_system_settings_and_replace_logo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        Storage::disk('public')->put('settings/old-logo.png', 'old');
        Setting::create([
            'key' => 'app_logo',
            'value' => 'settings/old-logo.png',
            'type' => 'file',
        ]);

        $this->actingAs($admin)
            ->put(route('settings.update'), [
                'app_name' => 'Cirengit App',
                'organization_name' => 'PJ Pemuda Persis Cirengit',
                'theme_mode' => 'dark',
                'default_attendance_radius' => 150,
                'default_attendance_open_minutes_before' => 45,
                'default_attendance_close_minutes_after' => 60,
                'default_location_accuracy_tolerance' => 25,
                'whatsapp_group_reminder_template' => "Reminder: {nama_kegiatan}\nTopik: {topic}\nLink: {link_presensi}",
                'app_logo' => $this->fakePng('logo.png'),
                'login_logo' => $this->fakePng('login-logo.png'),
            ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('success', 'Pengaturan sistem berhasil diperbarui.');

        $this->assertDatabaseHas('settings', [
            'key' => 'app_name',
            'value' => 'Cirengit App',
            'type' => 'string',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'organization_name',
            'value' => 'PJ Pemuda Persis Cirengit',
            'type' => 'string',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'theme_mode',
            'value' => 'dark',
            'type' => 'string',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'default_attendance_radius',
            'value' => '150',
            'type' => 'integer',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'default_attendance_open_minutes_before',
            'value' => '45',
            'type' => 'integer',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'default_attendance_close_minutes_after',
            'value' => '60',
            'type' => 'integer',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'default_location_accuracy_tolerance',
            'value' => '25',
            'type' => 'integer',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'whatsapp_group_reminder_template',
            'value' => "Reminder: {nama_kegiatan}\nTopik: {topic}\nLink: {link_presensi}",
            'type' => 'text',
        ]);

        $appLogo = Setting::where('key', 'app_logo')->first();
        $loginLogo = Setting::where('key', 'login_logo')->first();

        $this->assertNotSame('settings/old-logo.png', $appLogo->value);
        Storage::disk('public')->assertMissing('settings/old-logo.png');
        Storage::disk('public')->assertExists($appLogo->value);
        Storage::disk('public')->assertExists($loginLogo->value);
    }

    public function test_system_setting_logo_validation_rejects_invalid_file(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('settings.update'), [
                'app_name' => 'Cirengit App',
                'organization_name' => 'PJ Pemuda Persis Cirengit',
                'theme_mode' => 'light',
                'default_attendance_radius' => 100,
                'default_attendance_open_minutes_before' => 30,
                'default_attendance_close_minutes_after' => 30,
                'default_location_accuracy_tolerance' => 50,
                'app_logo' => UploadedFile::fake()->create('logo.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('app_logo');
    }

    public function test_system_setting_attendance_defaults_validation_rejects_invalid_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('settings.update'), [
                'app_name' => 'Cirengit App',
                'organization_name' => 'PJ Pemuda Persis Cirengit',
                'theme_mode' => 'light',
                'default_attendance_radius' => 0,
                'default_attendance_open_minutes_before' => -1,
                'default_attendance_close_minutes_after' => 'abc',
                'default_location_accuracy_tolerance' => -1,
            ])
            ->assertSessionHasErrors([
                'default_attendance_radius',
                'default_attendance_open_minutes_before',
                'default_attendance_close_minutes_after',
                'default_location_accuracy_tolerance',
            ]);
    }

    private function fakePng(string $name): UploadedFile
    {
        $onePixelPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $onePixelPng);
    }
}
