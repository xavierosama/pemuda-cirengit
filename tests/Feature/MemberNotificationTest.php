<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MemberNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_dashboard_generates_relevant_notifications(): void
    {
        Carbon::setTestNow('2026-07-14 19:45:00');

        $member = Member::create([
            'full_name' => 'Anggota Notifikasi',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
        ]);
        $activity = Activity::create([
            'title' => 'Kajian Notifikasi',
            'activity_date' => '2026-07-14',
            'start_time' => '20:00',
            'end_time' => '20:10',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
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

        $this->actingAs($user)
            ->get(route('member.home'))
            ->assertOk()
            ->assertSee('Buka notifikasi')
            ->assertSee('Notifikasi');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'member_id' => $member->id,
            'type' => 'attendance_opened',
            'read_at' => null,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'attendance_not_submitted',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'attendance_closing_soon',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'profile_incomplete',
        ]);

        Carbon::setTestNow();
    }

    public function test_member_can_view_and_mark_notifications_as_read(): void
    {
        $member = Member::create(['full_name' => 'Pembaca Notifikasi', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);
        $notification = Notification::create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'type' => 'profile_incomplete',
            'dedupe_key' => 'profile_incomplete:member',
            'title' => 'Lengkapi profil anggota',
            'message' => 'Lengkapi profil Anda.',
            'url' => route('member.profile.edit', absolute: false),
        ]);

        $this->actingAs($user)
            ->get(route('member.notifications.index'))
            ->assertOk()
            ->assertSee('Lengkapi profil anggota')
            ->assertSee('Belum dibaca');

        $this->actingAs($user)
            ->post(route('member.notifications.read', $notification), [
                'redirect_to' => route('member.home', absolute: false),
            ])
            ->assertRedirect(route('member.home', absolute: false));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_member_can_mark_all_own_notifications_as_read_and_cannot_read_others(): void
    {
        $member = Member::create(['full_name' => 'Pemilik Notifikasi', 'member_status' => 'active']);
        $otherMember = Member::create(['full_name' => 'Member Lain', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);
        $otherUser = User::factory()->create(['member_id' => $otherMember->id, 'role' => 'member']);

        Notification::create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'type' => 'activity_reminder',
            'dedupe_key' => 'activity_reminder:1',
            'title' => 'Reminder',
            'message' => 'Pesan untuk pemilik.',
        ]);
        $otherNotification = Notification::create([
            'user_id' => $otherUser->id,
            'member_id' => $otherMember->id,
            'type' => 'activity_reminder',
            'dedupe_key' => 'activity_reminder:2',
            'title' => 'Reminder lain',
            'message' => 'Pesan untuk member lain.',
        ]);

        $this->actingAs($user)
            ->post(route('member.notifications.read', $otherNotification))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('member.notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, Notification::where('user_id', $user->id)->unread()->count());
        $this->assertSame(1, Notification::where('user_id', $otherUser->id)->unread()->count());
    }
}
