<?php

namespace Tests\Feature;

use App\Models\ActivityLocation;
use App\Models\AgendaSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLocationCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_activity_locations(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('activity-locations.store'), [
            'name' => 'Masjid Cirengit',
            'address' => 'Komplek Pemuda Persis Cirengit',
            'latitude' => '-6.1234567',
            'longitude' => '107.1234567',
            'radius_meters' => 120,
            'is_active' => 1,
        ])->assertRedirect(route('activity-locations.index'));

        $location = ActivityLocation::where('name', 'Masjid Cirengit')->firstOrFail();

        $this->actingAs($user)
            ->get(route('activity-locations.index'))
            ->assertOk()
            ->assertSee('Lokasi Kegiatan')
            ->assertSee('Masjid Cirengit')
            ->assertSee('120 m');

        $this->actingAs($user)->put(route('activity-locations.update', $location), [
            'name' => 'Masjid Cirengit Baru',
            'address' => 'Alamat diperbarui',
            'latitude' => '-6.7654321',
            'longitude' => '107.7654321',
            'radius_meters' => 150,
            'is_active' => 1,
        ])->assertRedirect(route('activity-locations.index'));

        $this->assertDatabaseHas('activity_locations', [
            'id' => $location->id,
            'name' => 'Masjid Cirengit Baru',
            'radius_meters' => 150,
            'is_active' => 1,
        ]);

        $this->actingAs($user)
            ->patch(route('activity-locations.deactivate', $location))
            ->assertRedirect(route('activity-locations.index'));

        $this->assertFalse($location->fresh()->is_active);
    }

    public function test_agenda_schedule_form_uses_time_picker_and_location_dropdown(): void
    {
        $user = User::factory()->create();
        $location = ActivityLocation::create([
            'name' => 'Sekretariat Pemuda',
            'address' => 'Sekretariat utama',
            'latitude' => '-6.1111111',
            'longitude' => '107.2222222',
            'radius_meters' => 90,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('agenda-schedules.create'))
            ->assertOk()
            ->assertSee('js-time-picker', false)
            ->assertSee('activity_location_id', false)
            ->assertSee('Sekretariat Pemuda');

        $this->actingAs($user)->post(route('agenda-schedules.store'), [
            'title' => 'Kajian Lokasi Terstruktur',
            'schedule_type' => 'weekly',
            'day_of_week' => 2,
            'start_time' => '20:00',
            'end_time' => '22:00',
            'activity_location_id' => $location->id,
            'default_radius' => 100,
            'is_active' => 1,
        ])->assertRedirect(route('agenda-schedules.index'));

        $agendaSchedule = AgendaSchedule::where('title', 'Kajian Lokasi Terstruktur')->firstOrFail();

        $this->assertSame($location->id, $agendaSchedule->activity_location_id);
        $this->assertSame('Sekretariat Pemuda', $agendaSchedule->default_location);
        $this->assertSame('90', (string) $agendaSchedule->default_radius);
        $this->assertSame('-6.1111111', (string) $agendaSchedule->default_latitude);
        $this->assertSame('107.2222222', (string) $agendaSchedule->default_longitude);

        $this->actingAs($user)
            ->get(route('agenda-schedules.edit', $agendaSchedule))
            ->assertOk()
            ->assertSee('value="'.$location->id.'"', false)
            ->assertSee('value="20:00"', false)
            ->assertSee('value="22:00"', false);
    }
}
