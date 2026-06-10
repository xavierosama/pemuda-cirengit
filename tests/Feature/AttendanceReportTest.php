<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_internal_user_can_view_default_monthly_attendance_report(): void
    {
        Carbon::setTestNow('2026-06-15 10:00:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $data = $this->seedReportData($admin);

        $response = $this->actingAs($admin)->get(route('attendance-reports.index'));

        $response->assertOk()
            ->assertSee('Rekap Presensi')
            ->assertSee('10/06/2026')
            ->assertSee('Rapat Dakwah')
            ->assertSee('Kajian Pendidikan')
            ->assertSee('NPA-001')
            ->assertSee('cdn.jsdelivr.net/npm/chart.js', false)
            ->assertViewHas('summary', fn (array $summary) => $summary === [
                'total_activities' => 2,
                'total_active_members' => 3,
                'present' => 3,
                'permission' => 1,
                'absent' => 1,
                'need_verification' => 1,
                'attendance_percentage' => 50.0,
                'total_potential_attendances' => 6,
            ])
            ->assertViewHas('activityRows', fn ($rows) => $rows->count() === 2
                && $rows->first()['activity']->is($data['dakwahActivity'])
                && $rows->first()['counts']['present'] === 1
                && $rows->first()['counts']['permission'] === 1
                && $rows->first()['counts']['absent'] === 1
                && $rows->first()['attendance_percentage'] === 33.33)
            ->assertViewHas('memberRows', fn ($rows) => $rows->count() === 3
                && $rows->first()['member']->is($data['firstMember'])
                && $rows->first()['counts']['present'] === 2
                && $rows->first()['attendance_percentage'] === 100.0);
    }

    public function test_attendance_report_can_be_filtered_by_department(): void
    {
        Carbon::setTestNow('2026-06-15 10:00:00');
        $secretary = User::factory()->create(['role' => 'secretary']);
        $data = $this->seedReportData($secretary);

        $response = $this->actingAs($secretary)->get(route('attendance-reports.index', [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'department_id' => $data['dakwah']->id,
        ]));

        $response->assertOk()
            ->assertSee('Rapat Dakwah')
            ->assertViewHas('summary', fn (array $summary) => $summary['total_activities'] === 1
                && $summary['total_active_members'] === 2
                && $summary['present'] === 1
                && $summary['permission'] === 1
                && $summary['attendance_percentage'] === 50.0)
            ->assertViewHas('activityRows', fn ($rows) => $rows->count() === 1
                && $rows->first()['activity']->is($data['dakwahActivity']))
            ->assertViewHas('memberRows', fn ($rows) => $rows->count() === 2);
    }

    public function test_member_cannot_access_attendance_report(): void
    {
        $member = Member::create(['full_name' => 'Anggota', 'member_status' => 'active']);
        $user = User::factory()->create(['role' => 'member', 'member_id' => $member->id]);

        $this->actingAs($user)
            ->get(route('attendance-reports.index'))
            ->assertRedirect(route('member.home'))
            ->assertSessionHas('warning', 'Dashboard admin hanya dapat diakses oleh pengurus.');
    }

    private function seedReportData(User $user): array
    {
        $dakwah = Department::create(['name' => 'Dakwah', 'status' => 'active']);
        $pendidikan = Department::create(['name' => 'Pendidikan', 'status' => 'active']);

        $firstMember = Member::create([
            'department_id' => $dakwah->id,
            'full_name' => 'Ahmad Dakwah',
            'npa' => 'NPA-001',
            'member_status' => 'active',
        ]);
        $secondMember = Member::create([
            'department_id' => $dakwah->id,
            'full_name' => 'Budi Dakwah',
            'npa' => 'NPA-002',
            'member_status' => 'active',
        ]);
        $thirdMember = Member::create([
            'department_id' => $pendidikan->id,
            'full_name' => 'Cici Pendidikan',
            'npa' => 'NPA-003',
            'member_status' => 'active',
        ]);
        Member::create([
            'department_id' => $dakwah->id,
            'full_name' => 'Doni Nonaktif',
            'member_status' => 'inactive',
        ]);

        $dakwahActivity = Activity::create([
            'department_id' => $dakwah->id,
            'title' => 'Rapat Dakwah',
            'activity_date' => '2026-06-10',
            'start_time' => '20:00',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'created_by' => $user->id,
        ]);
        $educationActivity = Activity::create([
            'department_id' => $pendidikan->id,
            'title' => 'Kajian Pendidikan',
            'activity_date' => '2026-06-20',
            'start_time' => '08:00',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'created_by' => $user->id,
        ]);
        Activity::create([
            'department_id' => $dakwah->id,
            'title' => 'Kegiatan Bulan Lalu',
            'activity_date' => '2026-05-20',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'created_by' => $user->id,
        ]);

        Attendance::create(['activity_id' => $dakwahActivity->id, 'member_id' => $firstMember->id, 'status' => 'present', 'attendance_method' => 'manual']);
        Attendance::create(['activity_id' => $dakwahActivity->id, 'member_id' => $secondMember->id, 'status' => 'permission', 'attendance_method' => 'manual']);
        Attendance::create(['activity_id' => $dakwahActivity->id, 'member_id' => $thirdMember->id, 'status' => 'absent', 'attendance_method' => 'manual']);
        Attendance::create(['activity_id' => $educationActivity->id, 'member_id' => $firstMember->id, 'status' => 'present', 'attendance_method' => 'manual']);
        Attendance::create(['activity_id' => $educationActivity->id, 'member_id' => $secondMember->id, 'status' => 'need_verification', 'attendance_method' => 'manual']);
        Attendance::create(['activity_id' => $educationActivity->id, 'member_id' => $thirdMember->id, 'status' => 'present', 'attendance_method' => 'manual']);

        return compact('dakwah', 'pendidikan', 'firstMember', 'secondMember', 'thirdMember', 'dakwahActivity', 'educationActivity');
    }
}
