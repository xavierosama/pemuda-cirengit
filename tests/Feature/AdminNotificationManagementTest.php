<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\Department;
use App\Models\Member;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_draft_and_send_notification_to_active_members(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetMember = Member::create(['full_name' => 'Target Aktif', 'member_status' => 'active']);
        $targetUser = User::factory()->create(['member_id' => $targetMember->id, 'role' => 'member']);
        Member::create(['full_name' => 'Aktif Tanpa Akun', 'member_status' => 'active']);
        $inactiveMember = Member::create(['full_name' => 'Tidak Aktif', 'member_status' => 'inactive']);
        User::factory()->create(['member_id' => $inactiveMember->id, 'role' => 'member']);

        $this->actingAs($admin)
            ->post(route('admin-notifications.store'), [
                'title' => 'Pengumuman Kajian',
                'message' => 'Kajian dimulai tepat waktu.',
                'type' => 'announcement',
                'target_type' => 'active_members',
                'channel' => 'both',
                'url' => '/member',
                'submit_action' => 'draft',
            ])
            ->assertRedirect();

        $adminNotification = AdminNotification::firstOrFail();

        $this->assertSame('draft', $adminNotification->status);

        $this->actingAs($admin)
            ->post(route('admin-notifications.send', $adminNotification))
            ->assertRedirect(route('admin-notifications.show', $adminNotification));

        $adminNotification->refresh();

        $this->assertSame('sent', $adminNotification->status);
        $this->assertSame(2, $adminNotification->target_count);
        $this->assertSame(1, $adminNotification->delivered_count);
        $this->assertSame(1, $adminNotification->skipped_count);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $targetUser->id,
            'member_id' => $targetMember->id,
            'type' => 'admin_announcement',
            'title' => 'Pengumuman Kajian',
            'read_at' => null,
        ]);
    }

    public function test_admin_notification_can_target_department_members_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $education = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $social = Department::create(['name' => 'Sosial', 'status' => 'active']);
        $educationMember = Member::create([
            'department_id' => $education->id,
            'full_name' => 'Member Pendidikan',
            'member_status' => 'active',
        ]);
        $socialMember = Member::create([
            'department_id' => $social->id,
            'full_name' => 'Member Sosial',
            'member_status' => 'active',
        ]);
        $educationUser = User::factory()->create(['member_id' => $educationMember->id, 'role' => 'member']);
        $socialUser = User::factory()->create(['member_id' => $socialMember->id, 'role' => 'member']);

        $this->actingAs($admin)
            ->post(route('admin-notifications.store'), [
                'title' => 'Info Bidang Pendidikan',
                'message' => 'Pesan khusus bidang.',
                'type' => 'general',
                'target_type' => 'by_bidang',
                'department_ids' => [$education->id],
                'channel' => 'in_app',
                'submit_action' => 'send',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $educationUser->id,
            'title' => 'Info Bidang Pendidikan',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $socialUser->id,
            'title' => 'Info Bidang Pendidikan',
        ]);
    }

    public function test_admin_notification_can_target_all_members_including_inactive(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $activeMember = Member::create(['full_name' => 'Member Aktif', 'member_status' => 'active']);
        $inactiveMember = Member::create(['full_name' => 'Member Tidak Aktif', 'member_status' => 'inactive']);
        $activeUser = User::factory()->create(['member_id' => $activeMember->id, 'role' => 'member']);
        $inactiveUser = User::factory()->create(['member_id' => $inactiveMember->id, 'role' => 'member']);

        $this->actingAs($admin)
            ->post(route('admin-notifications.store'), [
                'title' => 'Info Semua Anggota',
                'message' => 'Pesan untuk semua status anggota.',
                'type' => 'organization_info',
                'target_type' => 'all_members',
                'channel' => 'in_app',
                'submit_action' => 'send',
            ])
            ->assertRedirect();

        $adminNotification = AdminNotification::firstOrFail();

        $this->assertSame(2, $adminNotification->target_count);
        $this->assertSame(2, $adminNotification->delivered_count);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $activeUser->id,
            'type' => 'organization_info',
            'title' => 'Info Semua Anggota',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $inactiveUser->id,
            'type' => 'organization_info',
            'title' => 'Info Semua Anggota',
        ]);
    }

    public function test_admin_notification_with_empty_target_is_not_marked_sent(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $emptyDepartment = Department::create(['name' => 'Bidang Kosong', 'status' => 'active']);

        $this->actingAs($admin)
            ->from(route('admin-notifications.create'))
            ->post(route('admin-notifications.store'), [
                'title' => 'Tidak Ada Target',
                'message' => 'Pesan ini tidak punya target.',
                'type' => 'urgent',
                'target_type' => 'by_bidang',
                'department_ids' => [$emptyDepartment->id],
                'channel' => 'both',
                'submit_action' => 'send',
            ])
            ->assertRedirect(route('admin-notifications.create'))
            ->assertSessionHasErrors('target_type');

        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Tidak Ada Target',
            'status' => 'draft',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'title' => 'Tidak Ada Target',
        ]);
    }

    public function test_member_cannot_access_admin_notification_management(): void
    {
        $member = Member::create(['full_name' => 'Member Biasa', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);

        $this->actingAs($user)
            ->get(route('admin-notifications.index'))
            ->assertRedirect(route('member.home'));
    }
}
