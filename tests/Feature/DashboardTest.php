<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AgendaSchedule;
use App\Models\Attendance;
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
            ->assertSee('Belum ada kegiatan yang akan datang.')
            ->assertSee('Belum ada kegiatan dengan data presensi.')
            ->assertSee('Belum ada jadwal agenda aktif.');
    }

    public function test_dashboard_displays_statistics_and_limits_summary_lists(): void
    {
        Carbon::setTestNow('2026-06-10 10:00:00');
        $user = User::factory()->create();
        $members = collect();

        for ($index = 1; $index <= 7; $index++) {
            $members->push(Member::create([
                'full_name' => 'Anggota '.$index,
                'member_status' => $index <= 6 ? 'active' : 'inactive',
            ]));

            AgendaSchedule::create([
                'title' => 'Agenda Aktif '.$index,
                'schedule_type' => 'daily',
                'is_active' => $index <= 6,
                'created_by' => $user->id,
            ]);
        }

        for ($index = 1; $index <= 6; $index++) {
            $activity = Activity::create([
                'title' => 'Kegiatan Mendatang '.$index,
                'activity_date' => '2026-06-'.str_pad((string) (10 + $index), 2, '0', STR_PAD_LEFT),
                'status' => $index === 1 ? 'holiday' : 'scheduled',
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
                'active_agenda_schedules' => 6,
                'monthly_activities' => 6,
                'scheduled_activities' => 5,
                'holiday_activities' => 1,
                'monthly_attendances' => 6,
            ])
            ->assertViewHas('upcomingActivities', fn ($activities) => $activities->count() === 5
                && $activities->pluck('title')->all() === [
                    'Kegiatan Mendatang 1',
                    'Kegiatan Mendatang 2',
                    'Kegiatan Mendatang 3',
                    'Kegiatan Mendatang 4',
                    'Kegiatan Mendatang 5',
                ])
            ->assertViewHas('recentAttendanceActivities', fn ($activities) => $activities->count() === 5
                && $activities->first()->title === 'Kegiatan Mendatang 6'
                && $activities->first()->need_verification_count === 1)
            ->assertViewHas('activeAgendaSchedules', fn ($schedules) => $schedules->count() === 5)
            ->assertSee('Kegiatan Mendatang 1');
    }
}
