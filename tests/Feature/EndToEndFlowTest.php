<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\AgendaSchedule;
use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EndToEndFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_member_attendance_report_and_export_flow_runs_end_to_end(): void
    {
        Carbon::setTestNow('2026-06-13 08:00:00');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $this->actingAs($admin)
            ->post(route('departments.store'), [
                'name' => 'Pendidikan',
                'description' => 'Bidang pendidikan',
                'status' => 'active',
            ])
            ->assertRedirect(route('departments.index'));
        $department = Department::where('name', 'Pendidikan')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('positions.store'), [
                'name' => 'Anggota',
                'description' => 'Anggota aktif',
                'status' => 'active',
            ])
            ->assertRedirect(route('positions.index'));
        $position = Position::where('name', 'Anggota')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('members.store'), [
                'department_id' => $department->id,
                'position_id' => $position->id,
                'full_name' => 'Ahmad End To End',
                'npa' => '20.0001',
                'phone' => '081234567890',
                'email' => 'ahmad.e2e@example.test',
                'address' => 'Kp. Cirengit',
                'joined_at' => '2026-06-10',
                'member_status' => 'active',
                'notes' => 'Anggota untuk flow e2e',
            ])
            ->assertRedirect(route('members.index'));
        $member = Member::where('npa', '20.0001')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('members.account.store', $member))
            ->assertSessionHas('success');
        $memberUser = User::where('email', 'ahmad.e2e@example.test')->firstOrFail();
        $this->assertSame('member', $memberUser->role);
        $this->assertSame($member->id, $memberUser->member_id);

        $this->actingAs($memberUser)
            ->get(route('member.home'))
            ->assertOk()
            ->assertSee('Assalamu')
            ->assertSee('20.0001');

        $this->actingAs($admin)
            ->post(route('agenda-schedules.store'), [
                'title' => 'Kajian End To End',
                'description' => 'Agenda rutin',
                'department_id' => $department->id,
                'pic_id' => $member->id,
                'schedule_type' => 'weekly',
                'day_of_week' => 6,
                'day_of_month' => null,
                'specific_date' => null,
                'start_time' => '08:00',
                'end_time' => '09:00',
                'default_location' => 'Masjid Cirengit',
                'default_latitude' => '-6.2000000',
                'default_longitude' => '107.2000000',
                'default_radius' => 100,
                'is_active' => 1,
            ])
            ->assertRedirect(route('agenda-schedules.index'));
        $agendaSchedule = AgendaSchedule::where('title', 'Kajian End To End')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('agenda-schedules.activities.store', $agendaSchedule), [
                'activity_date' => '2026-06-13',
            ])
            ->assertRedirect();
        $activity = Activity::where('agenda_schedule_id', $agendaSchedule->id)->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('activities.update', $activity), [
                'agenda_schedule_id' => $agendaSchedule->id,
                'department_id' => $department->id,
                'pic_id' => $member->id,
                'title' => $activity->title,
                'activity_date' => '2026-06-13',
                'start_time' => '08:00',
                'end_time' => '09:00',
                'location' => 'Masjid Cirengit',
                'latitude' => '-6.2000000',
                'longitude' => '107.2000000',
                'attendance_radius' => 100,
                'status' => 'scheduled',
                'change_reason' => null,
                'attendance_enabled' => 1,
                'attendance_open_at' => '2026-06-13 07:30:00',
                'attendance_close_at' => '2026-06-13 09:30:00',
            ])
            ->assertRedirect(route('activities.show', $activity));
        $activity->refresh();
        $this->assertTrue($activity->attendance_enabled);
        $this->assertNotNull($activity->attendance_token);

        $this->actingAs($admin)
            ->get(route('activities.attendance-qr', $activity))
            ->assertOk()
            ->assertSee('QR Presensi Kegiatan')
            ->assertSee(route('attendance.check-in.show', $activity->attendance_token, true));

        $this->actingAs($memberUser)
            ->get(route('attendance.check-in.show', $activity->attendance_token))
            ->assertOk()
            ->assertSee('Saya Hadir');

        $this->actingAs($memberUser)
            ->post(route('attendance.check-in.store', $activity->attendance_token), $this->nearbyLocation())
            ->assertRedirect(route('attendance.check-in.show', $activity->attendance_token))
            ->assertSessionHas('success');
        $this->assertSame(1, Attendance::where('activity_id', $activity->id)->where('member_id', $member->id)->count());
        $this->assertDatabaseHas('attendances', [
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'present',
            'attendance_method' => 'link',
            'verification_status' => 'valid',
        ]);

        $this->actingAs($memberUser)
            ->post(route('attendance.check-in.store', $activity->attendance_token), $this->nearbyLocation())
            ->assertSessionHas('info');
        $this->assertSame(1, Attendance::where('activity_id', $activity->id)->where('member_id', $member->id)->count());

        $dashboardActivity = Activity::create([
            'department_id' => $department->id,
            'pic_id' => $member->id,
            'title' => 'Presensi Dashboard Member',
            'activity_date' => '2026-06-13',
            'start_time' => '08:15',
            'end_time' => '09:15',
            'location' => 'Aula Cirengit',
            'latitude' => '-6.2000000',
            'longitude' => '107.2000000',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'attendance_open_at' => '2026-06-13 07:30:00',
            'attendance_close_at' => '2026-06-13 09:30:00',
            'attendance_token' => 'dashboard-member-token',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($memberUser)
            ->get(route('member.home'))
            ->assertOk()
            ->assertSee('Presensi Dashboard Member')
            ->assertSee('Saya Hadir');

        $this->actingAs($memberUser)
            ->post(route('member.activities.check-in', $dashboardActivity), $this->nearbyLocation())
            ->assertRedirect(route('member.home'))
            ->assertSessionHas('success');
        $this->assertSame(1, Attendance::where('activity_id', $dashboardActivity->id)->where('member_id', $member->id)->count());

        $existingAttendance = Attendance::where('activity_id', $activity->id)->where('member_id', $member->id)->firstOrFail();
        $this->actingAs($admin)
            ->post(route('activities.attendances.sync-participants', $activity))
            ->assertRedirect(route('activities.attendances.index', $activity))
            ->assertSessionHas('success');
        $this->assertSame('present', $existingAttendance->fresh()->status);
        $this->assertSame(1, Attendance::where('activity_id', $activity->id)->where('member_id', $member->id)->count());

        $this->actingAs($admin)
            ->get(route('activities.attendances.index', $activity))
            ->assertOk()
            ->assertSee('Kajian End To End')
            ->assertSee('Ahmad End To End');

        $this->actingAs($admin)
            ->put(route('attendances.update', $existingAttendance), [
                'status' => 'permission',
                'notes' => 'Izin setelah verifikasi manual',
            ])
            ->assertRedirect(route('activities.attendances.index', $activity->id));
        $this->assertDatabaseHas('attendances', [
            'id' => $existingAttendance->id,
            'status' => 'permission',
            'notes' => 'Izin setelah verifikasi manual',
        ]);

        $this->actingAs($admin)
            ->get(route('attendance-reports.index', [
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
                'department_id' => $department->id,
            ]))
            ->assertOk()
            ->assertSee('Rekap Presensi')
            ->assertSee('Kajian End To End')
            ->assertSee('Ahmad End To End');

        $this->actingAs($admin)
            ->get(route('members.export'))
            ->assertOk()
            ->assertDownload('data-anggota-pemuda-cirengit-2026-06-13.xlsx');

        $this->actingAs($admin)
            ->get(route('activities.attendances.export', $activity))
            ->assertOk()
            ->assertDownload('rekap-presensi-kajian-end-to-end-2026-06-13.xlsx');

        $this->actingAs($memberUser)
            ->get(route('dashboard'))
            ->assertRedirect(route('member.home'))
            ->assertSessionHas('warning', 'Dashboard admin hanya dapat diakses oleh pengurus.');
    }

    private function nearbyLocation(): array
    {
        return [
            'latitude' => -6.2000100,
            'longitude' => 107.2000100,
            'location_accuracy' => 8.5,
        ];
    }
}
