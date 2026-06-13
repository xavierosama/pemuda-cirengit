<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityAttendanceQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_qr_for_enabled_attendance_with_full_check_in_url(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $activity = Activity::create([
            'title' => 'Kajian QR',
            'activity_date' => '2026-06-20',
            'start_time' => '19:30',
            'end_time' => '21:00',
            'location' => 'Masjid Cirengit',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'attendance_token' => 'qr-attendance-token',
        ]);

        $attendanceUrl = route('attendance.check-in.show', $activity->attendance_token, true);

        $this->actingAs($user)
            ->get(route('activities.attendance-qr', $activity))
            ->assertOk()
            ->assertSee('QR Presensi Kegiatan')
            ->assertSee('Scan QR menggunakan kamera HP.')
            ->assertSee('Salin Link')
            ->assertSee($attendanceUrl)
            ->assertSee('data:image/svg+xml;base64,', false);
    }

    public function test_qr_page_generates_missing_token_when_attendance_is_enabled(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $activity = Activity::create([
            'title' => 'QR Tanpa Token',
            'activity_date' => '2026-06-20',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'attendance_token' => null,
        ]);

        $this->actingAs($user)
            ->get(route('activities.attendance-qr', $activity))
            ->assertOk();

        $this->assertNotNull($activity->fresh()->attendance_token);
    }

    public function test_qr_is_not_rendered_when_attendance_is_disabled(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $activity = Activity::create([
            'title' => 'Presensi Nonaktif',
            'activity_date' => '2026-06-20',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => false,
            'attendance_token' => 'inactive-token',
        ]);

        $this->actingAs($user)
            ->get(route('activities.attendance-qr', $activity))
            ->assertOk()
            ->assertSee('Presensi kegiatan belum aktif.')
            ->assertDontSee('data:image/svg+xml;base64,', false);
    }
}
