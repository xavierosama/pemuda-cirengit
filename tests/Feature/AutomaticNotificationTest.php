<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationRuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class AutomaticNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rule_service_creates_activity_notification_without_duplicates(): void
    {
        $member = Member::create([
            'full_name' => 'Anggota Aktif',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
        ]);
        $activity = $this->activity();

        $service = app(NotificationRuleService::class);
        $service->notifyActivityCreated($activity);
        $service->notifyActivityCreated($activity);

        $this->assertSame(1, Notification::where('user_id', $user->id)
            ->where('type', 'activity_created')
            ->where('related_type', 'activity')
            ->where('related_id', $activity->id)
            ->count());
    }

    public function test_activity_store_creates_automatic_notifications_for_active_members(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = Member::create([
            'full_name' => 'Target Notifikasi',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
        ]);

        $this->actingAs($admin)
            ->post(route('activities.store'), [
                'title' => 'Kajian Baru',
                'activity_date' => '2026-07-20',
                'start_time' => '20:00',
                'end_time' => '22:00',
                'location' => 'Masjid',
                'attendance_radius' => 100,
                'status' => 'scheduled',
            ])
            ->assertRedirect();

        $activity = Activity::where('title', 'Kajian Baru')->firstOrFail();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'member_id' => $member->id,
            'type' => 'activity_created',
            'related_type' => 'activity',
            'related_id' => $activity->id,
        ]);
    }

    public function test_outside_radius_check_in_creates_pending_verification_notification(): void
    {
        Carbon::setTestNow('2026-07-14 19:45:00');

        $member = Member::create([
            'full_name' => 'Butuh Verifikasi',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
        ]);
        $activity = $this->activity([
            'activity_date' => '2026-07-14',
            'latitude' => -6.9,
            'longitude' => 107.6,
            'attendance_open_at' => '2026-07-14 19:30:00',
            'attendance_close_at' => '2026-07-14 22:00:00',
        ]);
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'absent',
            'attendance_method' => 'manual',
            'verification_status' => 'valid',
        ]);

        $this->actingAs($user)
            ->post(route('attendance.check-in.store', $activity->attendance_token), [
                'latitude' => -7.5,
                'longitude' => 108.1,
                'location_accuracy' => 10,
            ])
            ->assertRedirect(route('member.home'));

        $attendance = Attendance::where('activity_id', $activity->id)
            ->where('member_id', $member->id)
            ->firstOrFail();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'member_id' => $member->id,
            'type' => 'attendance_pending_verification',
            'related_type' => 'attendance',
            'related_id' => $attendance->id,
        ]);

        Carbon::setTestNow();
    }

    public function test_admin_verification_actions_create_member_notifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = Member::create([
            'full_name' => 'Peserta Verifikasi',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
        ]);
        $activity = $this->activity();
        $attendance = Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'need_verification',
            'attendance_method' => 'link',
            'verification_status' => 'need_verification',
        ]);

        $this->actingAs($admin)
            ->patch(route('attendances.verify', $attendance))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'attendance_verified',
            'related_type' => 'attendance',
            'related_id' => $attendance->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('attendances.reject', $attendance->fresh()))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'attendance_rejected',
            'related_type' => 'attendance',
            'related_id' => $attendance->id,
        ]);
    }

    public function test_generate_reminders_command_creates_time_based_notifications_once(): void
    {
        Carbon::setTestNow('2026-07-14 19:45:00');

        $member = Member::create([
            'full_name' => 'Target Reminder',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
        ]);
        $activity = $this->activity([
            'activity_date' => '2026-07-14',
            'start_time' => '20:00',
            'end_time' => '20:10',
            'attendance_open_at' => '2026-07-14 19:30:00',
            'attendance_close_at' => '2026-07-14 20:10:00',
        ]);
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'absent',
            'attendance_method' => 'manual',
            'verification_status' => 'valid',
        ]);

        $this->artisan('notifications:generate-reminders')->assertSuccessful();
        $this->artisan('notifications:generate-reminders')->assertSuccessful();

        foreach (['attendance_opened', 'attendance_not_submitted', 'attendance_closing_soon'] as $type) {
            $this->assertSame(1, Notification::where('user_id', $user->id)
                ->where('type', $type)
                ->where('related_type', 'activity')
                ->where('related_id', $activity->id)
                ->count());
        }

        Carbon::setTestNow();
    }

    private function activity(array $overrides = []): Activity
    {
        return Activity::create(array_merge([
            'title' => 'Kajian Otomatis',
            'activity_date' => '2026-07-20',
            'start_time' => '20:00',
            'end_time' => '22:00',
            'location' => 'Masjid',
            'latitude' => -6.9,
            'longitude' => 107.6,
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'attendance_open_at' => '2026-07-20 19:30:00',
            'attendance_close_at' => '2026-07-20 22:00:00',
            'attendance_token' => 'token-'.Str::random(24),
        ], $overrides));
    }
}
