<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AgendaSchedule;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
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
            ->assertSee('Silakan akses presensi melalui QR atau link kegiatan yang diberikan pengurus.')
            ->assertSee('Profil Anggota')
            ->assertSee('Panduan Presensi')
            ->assertSee('Belum ada riwayat presensi.');
        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertRedirect(route('member.profile.edit'));
        $this->actingAs($user)
            ->get(route('attendance.check-in.show', $activity->attendance_token))
            ->assertOk()
            ->assertSee('Kegiatan Anggota');

        Carbon::setTestNow();
    }

    public function test_member_home_shows_profile_guidance_and_attendance_history(): void
    {
        Carbon::setTestNow('2026-06-13 08:00:00');
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $position = Position::create(['name' => 'Anggota', 'status' => 'active']);
        $member = Member::create([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'full_name' => 'Ahmad Anggota',
            'npa' => '20.0001',
            'email' => 'ahmad.member@example.test',
            'phone' => '081234567890',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'name' => 'Ahmad User',
            'email' => 'ahmad.member@example.test',
            'role' => 'member',
        ]);
        $activity = Activity::create([
            'title' => 'Kajian Member',
            'activity_date' => '2026-06-10',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
        ]);
        Activity::create([
            'department_id' => $department->id,
            'pic_id' => $member->id,
            'title' => 'Kajian Mendatang',
            'activity_date' => '2026-06-13',
            'start_time' => '20:00',
            'end_time' => '21:30',
            'location' => 'Masjid Cirengit',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
        ]);
        Activity::create([
            'department_id' => $department->id,
            'pic_id' => $member->id,
            'title' => 'Kajian Sedang Berlangsung',
            'activity_date' => '2026-06-13',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'location' => 'Aula Cirengit',
            'latitude' => '-6.2000000',
            'longitude' => '107.2000000',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'attendance_open_at' => '2026-06-13 07:30:00',
            'attendance_close_at' => '2026-06-13 09:30:00',
        ]);
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'present',
            'attendance_method' => 'link',
            'checked_in_at' => '2026-06-10 20:05:00',
            'verification_status' => 'valid',
        ]);

        $this->actingAs($user)
            ->get(route('member.home'))
            ->assertOk()
            ->assertSee("Assalamu'alaikum, Ahmad Anggota", false)
            ->assertSee('20.0001')
            ->assertSee('Pendidikan')
            ->assertSee('Anggota')
            ->assertSee('Scan QR atau buka link kegiatan')
            ->assertSee('Pastikan berada dalam radius lokasi kegiatan')
            ->assertSee('Kegiatan Sekarang')
            ->assertSee('Kajian Sedang Berlangsung')
            ->assertSee('Saya Hadir')
            ->assertSee('13/06/2026 07:30 - 13/06/2026 09:30')
            ->assertSee('Kegiatan Mendatang')
            ->assertSee('Kajian Mendatang')
            ->assertSee('Hari ini')
            ->assertSee('20:00 - 21:30')
            ->assertSee('Masjid Cirengit')
            ->assertSee('Kajian Member')
            ->assertSee('10/06/2026')
            ->assertSee('10/06/2026 20:05')
            ->assertSee('Hadir')
            ->assertSee('Valid')
            ->assertSee('aria-label="Buka menu akun"', false)
            ->assertSee('title="Buka menu akun"', false)
            ->assertSee('NPA: 20.0001')
            ->assertSee('Lihat Profile')
            ->assertSee('Edit Profile')
            ->assertSee('Logout');

        Carbon::setTestNow();
    }

    public function test_member_home_falls_back_to_active_agenda_schedules_when_no_upcoming_activity(): void
    {
        Carbon::setTestNow('2026-06-13 08:00:00');
        $department = Department::create(['name' => 'Dakwah', 'status' => 'active']);
        $member = Member::create(['full_name' => 'Anggota Agenda', 'member_status' => 'active']);
        $user = User::factory()->create(['member_id' => $member->id, 'role' => 'member']);
        Activity::create([
            'title' => 'Kegiatan Batal',
            'activity_date' => '2026-06-14',
            'attendance_radius' => 100,
            'status' => 'cancelled',
            'attendance_enabled' => false,
        ]);
        AgendaSchedule::create([
            'department_id' => $department->id,
            'title' => 'Kajian Rutin Dakwah',
            'schedule_type' => 'weekly',
            'day_of_week' => 5,
            'start_time' => '19:30',
            'end_time' => '21:00',
            'default_location' => 'Sekretariat',
            'default_radius' => 100,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('member.home'))
            ->assertOk()
            ->assertSee('Kegiatan Mendatang')
            ->assertSee('Kajian Rutin Dakwah')
            ->assertSee('Jadwal Rutin')
            ->assertSee('Mingguan')
            ->assertSee('19:30 - 21:00')
            ->assertSee('Sekretariat')
            ->assertSee('Dakwah')
            ->assertDontSee('Kegiatan Batal');

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
