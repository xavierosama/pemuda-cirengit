<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AgendaSchedule;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_is_safe_when_database_is_empty(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada kegiatan terdekat.')
            ->assertSee('Tidak ada presensi yang perlu diverifikasi.')
            ->assertSee('Aksi Cepat');
    }

    public function test_dashboard_displays_statistics_and_limits_summary_lists(): void
    {
        Carbon::setTestNow('2026-06-10 10:00:00');
        $user = User::factory()->create();
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        Department::create(['name' => 'Nonaktif', 'status' => 'inactive']);
        $members = collect();

        for ($index = 1; $index <= 7; $index++) {
            $members->push(Member::create([
                'full_name' => 'Anggota '.$index,
                'member_status' => $index <= 6 ? 'active' : 'inactive',
            ]));

            AgendaSchedule::create([
                'department_id' => $department->id,
                'title' => 'Agenda Aktif '.$index,
                'schedule_type' => 'weekly',
                'day_of_week' => 1,
                'start_time' => '08:00',
                'end_time' => '10:00',
                'default_location' => 'Sekretariat',
                'is_active' => $index <= 6,
                'created_by' => $user->id,
            ]);
        }

        for ($index = 1; $index <= 6; $index++) {
            $activity = Activity::create([
                'department_id' => $department->id,
                'title' => 'Kegiatan Mendatang '.$index,
                'topic' => $index === 1 ? 'Istifta & Keputusan Dewan Hisbah' : null,
                'description' => $index === 2 ? 'Kajian lanjutan untuk anggota.' : null,
                'activity_date' => '2026-06-'.str_pad((string) (10 + $index), 2, '0', STR_PAD_LEFT),
                'start_time' => '20:00',
                'end_time' => '21:00',
                'location' => 'Masjid Cirengit',
                'status' => $index === 1 ? 'holiday' : 'scheduled',
                'attendance_enabled' => $index !== 1,
                'attendance_open_at' => '2026-06-'.str_pad((string) (10 + $index), 2, '0', STR_PAD_LEFT).' 19:30:00',
                'attendance_close_at' => '2026-06-'.str_pad((string) (10 + $index), 2, '0', STR_PAD_LEFT).' 21:00:00',
                'created_by' => $user->id,
            ]);

            Attendance::create([
                'activity_id' => $activity->id,
                'member_id' => $members[$index - 1]->id,
                'status' => match ($index) {
                    1 => 'present',
                    2 => 'permission',
                    3 => 'absent',
                    default => 'need_verification',
                },
                'attendance_method' => 'manual',
                'verification_status' => $index >= 4 ? 'need_verification' : 'valid',
                'created_by' => $user->id,
            ]);
        }

        Activity::create([
            'title' => 'Kegiatan Bulan Lalu',
            'activity_date' => '2026-05-20',
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('statistics', fn (array $statistics) => $statistics === [
                'active_members' => 6,
                'members_without_account' => 6,
                'active_agenda_schedules' => 6,
                'monthly_activities' => 6,
                'need_verification_attendances' => 3,
            ])
            ->assertViewHas('upcomingActivities', fn ($activities) => $activities->count() === 5
                && $activities->pluck('title')->all() === [
                    'Kegiatan Mendatang 1',
                    'Kegiatan Mendatang 2',
                    'Kegiatan Mendatang 3',
                    'Kegiatan Mendatang 4',
                    'Kegiatan Mendatang 5',
                ])
            ->assertViewHas('monthlyAttendanceSummary', fn (array $summary) => $summary === [
                'present' => 1,
                'permission' => 1,
                'absent' => 1,
                'need_verification' => 3,
                'attendance_percentage' => 16.67,
            ])
            ->assertViewHas('needVerificationAttendances', fn ($attendances) => $attendances->count() === 3)
            ->assertSee('Ringkasan Utama')
            ->assertSee('Anggota Belum Punya Akun')
            ->assertSee('1')
            ->assertSee('Kegiatan Mendatang 1')
            ->assertSee('Topik: Istifta &amp; Keputusan Dewan Hisbah', false)
            ->assertSee('Kajian lanjutan untuk anggota.')
            ->assertSee('Kamis, 11/06/2026')
            ->assertSee('20:00 - 21:00')
            ->assertSee('Presensi')
            ->assertSee('Daftar Hadir')
            ->assertSee('Rekap Presensi Bulan Ini')
            ->assertSee('16.67%')
            ->assertSee('Presensi Perlu Verifikasi')
            ->assertSee('Aksi Cepat');
    }
}
