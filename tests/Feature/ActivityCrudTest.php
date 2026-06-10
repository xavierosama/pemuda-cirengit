<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AgendaSchedule;
use App\Models\Department;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_activity_and_generate_attendance_token(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'Dakwah', 'status' => 'active']);
        $pic = Member::create(['full_name' => 'Ahmad PIC', 'member_status' => 'active']);

        $this->actingAs($user)->post(route('activities.store'), [
            'department_id' => $department->id,
            'pic_id' => $pic->id,
            'title' => 'Kajian Aktual',
            'activity_date' => '2026-06-20',
            'start_time' => '19:30',
            'end_time' => '21:00',
            'location' => 'Masjid Cirengit',
            'latitude' => '-6.1234567',
            'longitude' => '107.1234567',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => 1,
            'attendance_open_at' => '2026-06-20 19:00',
            'attendance_close_at' => '2026-06-20 21:00',
        ])->assertRedirect();

        $activity = Activity::where('title', 'Kajian Aktual')->firstOrFail();
        $this->assertSame($user->id, $activity->created_by);
        $this->assertNotNull($activity->attendance_token);

        $this->actingAs($user)
            ->get(route('activities.index', ['search' => 'Kajian', 'activity_date' => '2026-06-20', 'department_id' => $department->id, 'status' => 'scheduled']))
            ->assertOk()
            ->assertSee('Kajian Aktual');

        $this->actingAs($user)->patch(route('activities.status.update', $activity), [
            'status' => 'relocated',
            'change_reason' => 'Lokasi utama tidak tersedia.',
        ])->assertRedirect(route('activities.show', $activity));

        $this->assertDatabaseHas('activities', ['id' => $activity->id, 'status' => 'relocated']);

        $this->actingAs($user)->delete(route('activities.destroy', $activity))
            ->assertRedirect(route('activities.index'));
        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    }

    public function test_activity_can_be_generated_from_agenda_schedule_defaults(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $pic = Member::create(['full_name' => 'PIC Pendidikan', 'member_status' => 'active']);
        $agendaSchedule = AgendaSchedule::create([
            'department_id' => $department->id,
            'pic_id' => $pic->id,
            'title' => 'Kajian Pendidikan',
            'schedule_type' => 'weekly',
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'default_location' => 'Sekretariat',
            'default_latitude' => '-6.2000000',
            'default_longitude' => '107.2000000',
            'default_radius' => 125,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('agenda-schedules.activities.create', $agendaSchedule))
            ->assertOk()
            ->assertSee('Tanggal Pelaksanaan');

        $this->actingAs($user)->post(route('agenda-schedules.activities.store', $agendaSchedule), [
            'activity_date' => '2026-06-22',
        ])->assertRedirect();

        $this->assertDatabaseHas('activities', [
            'agenda_schedule_id' => $agendaSchedule->id,
            'department_id' => $department->id,
            'pic_id' => $pic->id,
            'title' => 'Kajian Pendidikan',
            'location' => 'Sekretariat',
            'attendance_radius' => 125,
            'created_by' => $user->id,
        ]);

        $generatedActivity = Activity::where('agenda_schedule_id', $agendaSchedule->id)->firstOrFail();
        $this->assertSame('2026-06-22', $generatedActivity->activity_date->format('Y-m-d'));
    }

    public function test_activity_validation_rejects_invalid_attendance_period(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('activities.store'), [
            'title' => '',
            'activity_date' => 'invalid-date',
            'attendance_radius' => 0,
            'status' => 'unknown',
            'attendance_enabled' => 1,
            'attendance_open_at' => '2026-06-20 20:00',
            'attendance_close_at' => '2026-06-20 19:00',
        ])->assertSessionHasErrors(['title', 'activity_date', 'attendance_radius', 'status', 'attendance_close_at']);
    }

    public function test_attendance_close_time_is_allowed_without_open_time(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('activities.store'), [
            'title' => 'Presensi Tanpa Waktu Buka',
            'activity_date' => '2026-06-20',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => 1,
            'attendance_close_at' => '2026-06-20 21:00',
        ])->assertSessionDoesntHaveErrors('attendance_close_at');
    }
}
