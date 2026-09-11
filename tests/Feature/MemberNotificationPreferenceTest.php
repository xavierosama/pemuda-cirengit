<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\AdminNotificationDispatchService;
use App\Services\NotificationRuleService;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberNotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_view_and_update_notification_preferences(): void
    {
        $member = Member::create(['full_name' => 'Member Preferensi', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);

        $this->actingAs($user)
            ->get(route('member.notification-settings.edit'))
            ->assertOk()
            ->assertSee('Pengaturan Notifikasi')
            ->assertSee('Presensi dibuka')
            ->assertSee('Reminder profil');

        $payload = [
            'preferences' => collect(NotificationPreference::categories())
                ->mapWithKeys(fn (array $definition, string $category) => [
                    $category => [
                        'category' => $category,
                        'in_app_enabled' => '1',
                        'push_enabled' => $category === 'activity_reminder' ? '0' : '1',
                    ],
                ])
                ->all(),
        ];

        $this->actingAs($user)
            ->put(route('member.notification-settings.update'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'member_id' => $member->id,
            'category' => 'activity_reminder',
            'in_app_enabled' => true,
            'push_enabled' => false,
        ]);
    }

    public function test_disabled_push_preference_keeps_in_app_notification_without_sending_push(): void
    {
        $member = Member::create(['full_name' => 'Member Tanpa Push', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);
        NotificationPreference::create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'category' => 'attendance_opened',
            'in_app_enabled' => true,
            'push_enabled' => false,
        ]);
        $activity = Activity::create([
            'title' => 'Kajian Push Off',
            'activity_date' => now()->toDateString(),
            'start_time' => '20:00',
            'end_time' => '22:00',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'attendance_open_at' => now()->subMinute(),
            'attendance_close_at' => now()->addHour(),
        ]);
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'absent',
            'attendance_method' => 'manual',
            'verification_status' => 'valid',
        ]);

        $this->mock(PushNotificationService::class, function ($mock) {
            $mock->shouldReceive('sendToMemberUser')->never();
        });

        app(NotificationRuleService::class)->notifyAttendanceOpened($activity);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'member_id' => $member->id,
            'type' => 'attendance_opened',
            'related_type' => 'activity',
            'related_id' => $activity->id,
        ]);
    }

    public function test_push_throttle_blocks_repeated_push_for_same_category_and_activity(): void
    {
        $member = Member::create(['full_name' => 'Member Throttle', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);
        $activity = Activity::create([
            'title' => 'Kajian Throttle',
            'activity_date' => now()->toDateString(),
            'start_time' => '20:00',
            'end_time' => '22:00',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'attendance_open_at' => now()->subMinute(),
            'attendance_close_at' => now()->addHour(),
        ]);
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'absent',
            'attendance_method' => 'manual',
            'verification_status' => 'valid',
        ]);
        Notification::create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'type' => 'attendance_pending',
            'related_type' => 'activity',
            'related_id' => $activity->id,
            'dedupe_key' => 'legacy:attendance_pending:'.$activity->id,
            'title' => 'Legacy',
            'message' => 'Legacy reminder.',
            'created_at' => now()->subMinutes(5),
        ]);

        $this->mock(PushNotificationService::class, function ($mock) {
            $mock->shouldReceive('sendToMemberUser')->never();
        });

        app(NotificationRuleService::class)->notifyAttendanceNotSubmitted($activity);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'attendance_not_submitted',
            'related_type' => 'activity',
            'related_id' => $activity->id,
        ]);
    }

    public function test_admin_notification_push_respects_member_preference(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = Member::create(['full_name' => 'Target Pengumuman', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);
        NotificationPreference::create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'category' => 'admin_announcement',
            'in_app_enabled' => true,
            'push_enabled' => false,
        ]);
        $adminNotification = AdminNotification::create([
            'title' => 'Pengumuman Penting',
            'message' => 'Isi pengumuman.',
            'type' => 'announcement',
            'target_type' => 'all_members',
            'channel' => 'both',
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $this->mock(PushNotificationService::class, function ($mock) {
            $mock->shouldReceive('sendToMemberUser')->never();
        });

        app(AdminNotificationDispatchService::class)->dispatch($adminNotification);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'member_id' => $member->id,
            'type' => 'admin_announcement',
            'related_type' => 'admin_notification',
            'related_id' => $adminNotification->id,
        ]);
    }
}
