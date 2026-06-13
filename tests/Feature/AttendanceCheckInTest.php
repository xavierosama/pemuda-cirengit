<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceCheckInTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_check_in_link_requires_login_and_linked_member(): void
    {
        $activity = $this->createOpenActivity();

        $this->get(route('attendance.check-in.show', $activity->attendance_token))
            ->assertRedirect(route('login'));

        $user = User::factory()->create(['member_id' => null]);

        $this->actingAs($user)
            ->get(route('attendance.check-in.show', $activity->attendance_token))
            ->assertOk()
            ->assertSee('belum terhubung dengan data anggota');

        $this->actingAs($user)
            ->post(route('attendance.check-in.store', $activity->attendance_token), $this->nearbyLocation())
            ->assertSessionHas('error');

        $this->assertSame(0, Attendance::count());
    }

    public function test_check_in_respects_attendance_window_and_enabled_status(): void
    {
        Carbon::setTestNow('2026-06-25 10:00:00');
        [$user] = $this->createMemberUser();

        $notOpen = $this->createOpenActivity([
            'attendance_open_at' => '2026-06-25 11:00:00',
            'attendance_close_at' => '2026-06-25 12:00:00',
        ]);
        $closed = $this->createOpenActivity([
            'attendance_token' => 'closed-token',
            'attendance_open_at' => '2026-06-25 08:00:00',
            'attendance_close_at' => '2026-06-25 09:00:00',
        ]);
        $disabled = $this->createOpenActivity([
            'attendance_token' => 'disabled-token',
            'attendance_enabled' => false,
        ]);

        $this->actingAs($user)->get(route('attendance.check-in.show', $notOpen->attendance_token))->assertSee('Presensi belum dibuka.');
        $this->actingAs($user)->get(route('attendance.check-in.show', $closed->attendance_token))->assertSee('Presensi sudah ditutup.');
        $this->actingAs($user)->get(route('attendance.check-in.show', $disabled->attendance_token))->assertSee('Presensi belum diaktifkan');
    }

    public function test_member_inside_radius_is_recorded_as_valid_present(): void
    {
        Carbon::setTestNow('2026-06-25 10:00:00');
        [$user, $member] = $this->createMemberUser();
        $activity = $this->createOpenActivity();

        $this->actingAs($user)
            ->post(route('attendance.check-in.store', $activity->attendance_token), $this->nearbyLocation())
            ->assertRedirect(route('attendance.check-in.show', $activity->attendance_token))
            ->assertSessionHas('success');

        $attendance = Attendance::firstOrFail();
        $this->assertSame($member->id, $attendance->member_id);
        $this->assertSame('present', $attendance->status);
        $this->assertSame('link', $attendance->attendance_method);
        $this->assertSame('valid', $attendance->verification_status);
        $this->assertLessThanOrEqual(100, (float) $attendance->distance_from_activity);
        $this->assertSame('8.50', $attendance->location_accuracy);
    }

    public function test_member_check_in_can_update_synced_absent_attendance_to_present(): void
    {
        Carbon::setTestNow('2026-06-25 10:00:00');
        [$user, $member] = $this->createMemberUser();
        $activity = $this->createOpenActivity();
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'absent',
            'attendance_method' => 'manual',
            'verification_status' => 'valid',
        ]);

        $this->actingAs($user)
            ->post(route('attendance.check-in.store', $activity->attendance_token), $this->nearbyLocation())
            ->assertRedirect(route('attendance.check-in.show', $activity->attendance_token))
            ->assertSessionHas('success');

        $this->assertSame(1, Attendance::count());
        $attendance = Attendance::firstOrFail();
        $this->assertSame('present', $attendance->status);
        $this->assertSame('link', $attendance->attendance_method);
        $this->assertSame('valid', $attendance->verification_status);
        $this->assertNotNull($attendance->checked_in_at);
    }

    public function test_outside_radius_requires_verification_and_can_retry_before_admin_action(): void
    {
        Carbon::setTestNow('2026-06-25 10:00:00');
        [$user] = $this->createMemberUser();
        $activity = $this->createOpenActivity();

        $this->actingAs($user)
            ->post(route('attendance.check-in.store', $activity->attendance_token), [
                'latitude' => -6.2100000,
                'longitude' => 107.2000000,
                'location_accuracy' => 12,
            ])
            ->assertSessionHas('warning');

        $attendance = Attendance::firstOrFail();
        $this->assertSame('need_verification', $attendance->status);
        $this->assertSame('need_verification', $attendance->verification_status);

        $this->actingAs($user)
            ->post(route('attendance.check-in.store', $activity->attendance_token), $this->nearbyLocation())
            ->assertSessionHas('success');

        $this->assertSame(1, Attendance::count());
        $this->assertSame('present', $attendance->fresh()->status);

        $this->actingAs($user)
            ->post(route('attendance.check-in.store', $activity->attendance_token), [
                'latitude' => -6.2200000,
                'longitude' => 107.2000000,
                'location_accuracy' => 10,
            ])
            ->assertSessionHas('info');

        $this->assertSame(1, Attendance::count());
    }

    public function test_admin_can_verify_or_reject_pending_attendance(): void
    {
        $admin = User::factory()->create();
        [, $member] = $this->createMemberUser();
        $activity = $this->createOpenActivity();
        $attendance = Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'need_verification',
            'attendance_method' => 'link',
            'verification_status' => 'need_verification',
            'distance_from_activity' => 250,
        ]);

        $this->actingAs($admin)
            ->patch(route('attendances.verify', $attendance))
            ->assertSessionHas('success');

        $attendance->refresh();
        $this->assertSame('present', $attendance->status);
        $this->assertSame('valid', $attendance->verification_status);
        $this->assertSame($admin->id, $attendance->verified_by);
        $this->assertNotNull($attendance->verified_at);

        $attendance->update([
            'status' => 'need_verification',
            'verification_status' => 'need_verification',
            'verified_by' => null,
            'verified_at' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('attendances.reject', $attendance))
            ->assertSessionHas('success');

        $this->assertSame('rejected', $attendance->fresh()->verification_status);
    }

    public function test_member_dashboard_check_in_uses_radius_logic_and_updates_synced_absent_record(): void
    {
        Carbon::setTestNow('2026-06-25 10:00:00');
        [$user, $member] = $this->createMemberUser();
        $user->update(['role' => 'member']);
        $activity = $this->createOpenActivity();
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'absent',
            'attendance_method' => 'manual',
            'verification_status' => 'valid',
        ]);

        $this->actingAs($user)
            ->post(route('member.activities.check-in', $activity), $this->nearbyLocation())
            ->assertRedirect(route('member.home'))
            ->assertSessionHas('success');

        $this->assertSame(1, Attendance::count());
        $attendance = Attendance::firstOrFail();
        $this->assertSame('present', $attendance->status);
        $this->assertSame('link', $attendance->attendance_method);
        $this->assertSame('valid', $attendance->verification_status);
        $this->assertLessThanOrEqual(100, (float) $attendance->distance_from_activity);
        $this->assertSame('8.50', $attendance->location_accuracy);
    }

    public function test_member_dashboard_check_in_outside_radius_requires_verification_and_does_not_duplicate(): void
    {
        Carbon::setTestNow('2026-06-25 10:00:00');
        [$user] = $this->createMemberUser();
        $user->update(['role' => 'member']);
        $activity = $this->createOpenActivity();

        $this->actingAs($user)
            ->post(route('member.activities.check-in', $activity), [
                'latitude' => -6.2100000,
                'longitude' => 107.2000000,
                'location_accuracy' => 12,
            ])
            ->assertRedirect(route('member.home'))
            ->assertSessionHas('warning');

        $attendance = Attendance::firstOrFail();
        $this->assertSame('need_verification', $attendance->status);
        $this->assertSame('need_verification', $attendance->verification_status);

        $this->actingAs($user)
            ->post(route('member.activities.check-in', $activity), $this->nearbyLocation())
            ->assertSessionHas('info');

        $this->assertSame(1, Attendance::count());
        $this->assertSame('need_verification', $attendance->fresh()->status);
    }

    public function test_member_dashboard_check_in_requires_member_role(): void
    {
        Carbon::setTestNow('2026-06-25 10:00:00');
        [$user] = $this->createMemberUser();
        $user->update(['role' => 'admin']);
        $activity = $this->createOpenActivity();

        $this->actingAs($user)
            ->post(route('member.activities.check-in', $activity), $this->nearbyLocation())
            ->assertForbidden();
    }

    private function createMemberUser(): array
    {
        $member = Member::create(['full_name' => 'Anggota Login', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id]);

        return [$user, $member];
    }

    private function createOpenActivity(array $overrides = []): Activity
    {
        return Activity::create(array_merge([
            'title' => 'Kegiatan Check-in',
            'activity_date' => '2026-06-25',
            'latitude' => -6.2000000,
            'longitude' => 107.2000000,
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'attendance_open_at' => '2026-06-25 09:00:00',
            'attendance_close_at' => '2026-06-25 11:00:00',
            'attendance_token' => 'check-in-token',
        ], $overrides));
    }

    private function nearbyLocation(): array
    {
        return [
            'latitude' => -6.2001000,
            'longitude' => 107.2000000,
            'location_accuracy' => 8.5,
        ];
    }
}
