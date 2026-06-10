<?php

namespace Tests\Feature;

use App\Models\AgendaSchedule;
use App\Models\Department;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaScheduleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_and_deactivate_agenda_schedules(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'Dakwah', 'status' => 'active']);
        $pic = Member::create(['full_name' => 'Ahmad PIC', 'member_status' => 'active']);

        $this->actingAs($user)->post(route('agenda-schedules.store'), [
            'title' => 'Kajian Pekanan',
            'description' => 'Kajian rutin.',
            'department_id' => $department->id,
            'pic_id' => $pic->id,
            'schedule_type' => 'weekly',
            'day_of_week' => 5,
            'start_time' => '19:30',
            'end_time' => '21:00',
            'default_location' => 'Masjid Cirengit',
            'default_latitude' => '-6.1234567',
            'default_longitude' => '107.1234567',
            'default_radius' => 100,
            'is_active' => 1,
        ])->assertRedirect(route('agenda-schedules.index'));

        $agendaSchedule = AgendaSchedule::where('title', 'Kajian Pekanan')->firstOrFail();

        $this->assertSame($user->id, $agendaSchedule->created_by);
        $this->assertNull($agendaSchedule->specific_date);

        $this->actingAs($user)
            ->get(route('agenda-schedules.index', [
                'search' => 'Kajian',
                'department_id' => $department->id,
                'schedule_type' => 'weekly',
                'is_active' => '1',
            ]))
            ->assertOk()
            ->assertSee('Kajian Pekanan')
            ->assertSee('19:30')
            ->assertSee('21:00')
            ->assertSee('Dakwah')
            ->assertSee('Ahmad PIC');

        $this->actingAs($user)
            ->get(route('agenda-schedules.show', $agendaSchedule))
            ->assertOk()
            ->assertSee('19:30')
            ->assertSee('21:00')
            ->assertSee('Buat Kegiatan dari Jadwal');

        $this->actingAs($user)
            ->get(route('agenda-schedules.edit', $agendaSchedule))
            ->assertOk()
            ->assertSee('type="text"', false)
            ->assertSee('value="19:30"', false)
            ->assertSee('value="21:00"', false)
            ->assertSee('Gunakan format 24 jam, contoh 20:00.');

        $this->actingAs($user)->put(route('agenda-schedules.update', $agendaSchedule), [
            'title' => 'Kajian Bulanan',
            'schedule_type' => 'monthly',
            'day_of_month' => 15,
            'default_radius' => 150,
            'is_active' => 1,
        ])->assertRedirect(route('agenda-schedules.index'));

        $agendaSchedule->refresh();
        $this->assertSame('monthly', $agendaSchedule->schedule_type);
        $this->assertSame(15, $agendaSchedule->day_of_month);
        $this->assertNull($agendaSchedule->day_of_week);

        $this->actingAs($user)
            ->patch(route('agenda-schedules.deactivate', $agendaSchedule))
            ->assertRedirect(route('agenda-schedules.index'));

        $this->assertFalse($agendaSchedule->fresh()->is_active);

        $this->actingAs($user)
            ->get(route('agenda-schedules.activities.create', $agendaSchedule))
            ->assertOk()
            ->assertSee('Tanggal Pelaksanaan');
    }

    public function test_schedule_pattern_validation_is_conditional(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('agenda-schedules.store'), [
            'title' => 'Agenda Tanpa Tanggal',
            'schedule_type' => 'once',
            'default_radius' => 0,
            'is_active' => 1,
        ])->assertSessionHasErrors(['specific_date', 'default_radius']);

        $this->actingAs($user)->post(route('agenda-schedules.store'), [
            'title' => 'Agenda Mingguan',
            'schedule_type' => 'weekly',
            'default_radius' => 100,
            'is_active' => 1,
        ])->assertSessionHasErrors('day_of_week');

        $this->actingAs($user)->post(route('agenda-schedules.store'), [
            'title' => 'Agenda Bulanan',
            'schedule_type' => 'monthly',
            'default_radius' => 100,
            'is_active' => 1,
        ])->assertSessionHasErrors('day_of_month');
    }

    public function test_schedule_time_must_use_24_hour_hh_mm_format(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('agenda-schedules.store'), [
            'title' => 'Agenda AM PM',
            'schedule_type' => 'daily',
            'start_time' => '08:00 PM',
            'end_time' => '24:00',
            'default_radius' => 100,
            'is_active' => 1,
        ])->assertSessionHasErrors([
            'start_time' => 'Waktu mulai harus menggunakan format 24 jam HH:mm, contoh 20:00.',
            'end_time' => 'Waktu selesai harus menggunakan format 24 jam HH:mm, contoh 20:00.',
        ]);

        $this->actingAs($user)->post(route('agenda-schedules.store'), [
            'title' => 'Agenda Format 24 Jam',
            'schedule_type' => 'daily',
            'start_time' => '08:00',
            'end_time' => '23:59',
            'default_radius' => 100,
            'is_active' => 1,
        ])->assertRedirect(route('agenda-schedules.index'));

        $this->assertDatabaseHas('agenda_schedules', [
            'title' => 'Agenda Format 24 Jam',
            'start_time' => '08:00',
            'end_time' => '23:59',
        ]);
    }
}
