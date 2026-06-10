<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_reset_member_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = Member::create([
            'full_name' => 'Anggota Login',
            'npa' => 'NPA-LOGIN',
            'email' => 'anggota@example.test',
            'member_status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('members.account.store', $member))
            ->assertRedirect()
            ->assertSessionHas('success');

        $user = User::where('email', $member->email)->firstOrFail();
        $this->assertSame($member->id, $user->member_id);
        $this->assertSame('member', $user->role);
        $this->assertTrue(Hash::check('password', $user->password));

        $user->update(['password' => Hash::make('changed-password')]);

        $this->actingAs($admin)
            ->patch(route('members.account.reset-password', $member))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_account_requires_member_email_and_existing_user_is_linked_without_duplicate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = Member::create(['full_name' => 'Tanpa Email', 'member_status' => 'active']);

        $this->actingAs($admin)
            ->post(route('members.account.store', $member))
            ->assertSessionHasErrors('email');

        $member->update(['email' => 'existing@example.test']);
        $existingUser = User::factory()->create([
            'email' => 'existing@example.test',
            'member_id' => null,
            'role' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('members.account.store', $member))
            ->assertSessionHas('success');

        $this->assertSame(2, User::count());
        $this->assertSame($member->id, $existingUser->fresh()->member_id);
        $this->assertSame('member', $existingUser->fresh()->role);
    }

    public function test_member_id_is_unique_but_nullable_on_users(): void
    {
        $member = Member::create(['full_name' => 'Anggota Unique', 'member_status' => 'active']);

        User::factory()->create(['member_id' => null]);
        User::factory()->create(['member_id' => null]);
        User::factory()->create(['member_id' => $member->id]);

        $this->expectException(QueryException::class);

        User::factory()->create(['member_id' => $member->id]);
    }

    public function test_account_creation_stops_when_member_already_has_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = Member::create([
            'full_name' => 'Anggota Sudah Ada',
            'email' => 'sudah-ada@example.test',
            'member_status' => 'active',
        ]);
        User::factory()->create([
            'email' => $member->email,
            'member_id' => $member->id,
            'role' => 'member',
        ]);

        $this->actingAs($admin)
            ->post(route('members.account.store', $member))
            ->assertSessionHas('info', 'Anggota ini sudah memiliki akun login.');

        $this->assertSame(2, User::count());
    }

    public function test_member_detail_shows_account_status_and_conditional_buttons(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $memberWithoutAccount = Member::create([
            'full_name' => 'Belum Akun',
            'email' => 'belum-akun@example.test',
            'member_status' => 'active',
        ]);
        $memberWithAccount = Member::create([
            'full_name' => 'Sudah Akun',
            'email' => 'sudah-akun@example.test',
            'member_status' => 'active',
        ]);
        User::factory()->create([
            'email' => $memberWithAccount->email,
            'member_id' => $memberWithAccount->id,
            'role' => 'member',
        ]);

        $this->actingAs($admin)
            ->get(route('members.show', $memberWithoutAccount))
            ->assertOk()
            ->assertSee('Belum Ada')
            ->assertSee('NPA sebaiknya dilengkapi karena menjadi identitas utama anggota.')
            ->assertSee('Buat Akun Login')
            ->assertDontSee('Reset Password');

        $this->actingAs($admin)
            ->get(route('members.show', $memberWithAccount))
            ->assertOk()
            ->assertSee('Sudah Ada')
            ->assertSee('Reset Password')
            ->assertDontSee('Buat Akun Login');
    }

    public function test_member_role_cannot_access_admin_but_can_access_check_in(): void
    {
        Carbon::setTestNow('2026-06-25 10:00:00');
        $member = Member::create(['full_name' => 'Anggota Terbatas', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);
        $activity = Activity::create([
            'title' => 'Kegiatan Anggota',
            'activity_date' => '2026-06-25',
            'latitude' => -6.2,
            'longitude' => 107.2,
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'attendance_open_at' => '2026-06-25 09:00:00',
            'attendance_close_at' => '2026-06-25 11:00:00',
            'attendance_token' => 'member-access-token',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('member.home'))
            ->assertSessionHas('warning', 'Dashboard admin hanya dapat diakses oleh pengurus.');
        $this->actingAs($user)
            ->get(route('members.index'))
            ->assertRedirect(route('member.home'))
            ->assertSessionHas('warning', 'Dashboard admin hanya dapat diakses oleh pengurus.');
        $this->actingAs($user)
            ->get(route('member.home'))
            ->assertOk()
            ->assertSee('Silakan akses presensi melalui link atau QR kegiatan yang diberikan pengurus.');
        $this->actingAs($user)->get(route('profile.edit'))->assertOk();
        $this->actingAs($user)
            ->get(route('attendance.check-in.show', $activity->attendance_token))
            ->assertOk()
            ->assertSee('Kegiatan Anggota');

        Carbon::setTestNow();
    }

    public function test_secretary_can_access_internal_routes(): void
    {
        $secretary = User::factory()->create(['role' => 'secretary']);

        $this->actingAs($secretary)
            ->get(route('dashboard'))
            ->assertOk();
    }
}
