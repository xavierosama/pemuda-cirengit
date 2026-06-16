<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AgendaSchedule;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Models\Setting;
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
        ])->assertRedirect();

        $activity = Activity::where('title', 'Kajian Aktual')->firstOrFail();
        $this->assertSame($user->id, $activity->created_by);
        $this->assertNotNull($activity->attendance_token);

        $this->actingAs($user)
            ->get(route('activities.index', ['search' => 'Kajian', 'activity_date' => '2026-06-20', 'department_id' => $department->id, 'status' => 'scheduled']))
            ->assertOk()
            ->assertSee('Kelola kegiatan berjalan, perubahan jadwal, dan pengaturan presensi.')
            ->assertSee('Kegiatan Bulan Ini')
            ->assertSee('Presensi Terjadwal')
            ->assertSee('Filter Kegiatan Aktual')
            ->assertSee('Tabel Kegiatan Aktual')
            ->assertSee('Kajian Aktual');

        $this->actingAs($user)
            ->get(route('activities.index', [
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
                'attendance_enabled' => '1',
            ]))
            ->assertOk()
            ->assertSee('Kajian Aktual')
            ->assertSee('QR Presensi')
            ->assertSee('Daftar Hadir');

        $this->actingAs($user)
            ->get(route('activities.create'))
            ->assertOk()
            ->assertSee('Informasi Kegiatan')
            ->assertSee('Tanggal, Waktu, dan Lokasi')
            ->assertSee('Jadwal Presensi Otomatis')
            ->assertSee('placeholder="Contoh: 20:00"', false)
            ->assertSee('Dalam meter, contoh: 100.')
            ->assertSee('Presensi dihitung otomatis dari tanggal, waktu mulai, dan waktu selesai kegiatan.')
            ->assertDontSee('Presensi aktif / tidak aktif')
            ->assertDontSee('Waktu buka presensi')
            ->assertDontSee('Waktu tutup presensi');

        $this->actingAs($user)
            ->get(route('activities.edit', $activity))
            ->assertOk()
            ->assertSee('Edit Kegiatan Aktual')
            ->assertSee('Attendance token')
            ->assertSee('Batal/Kembali');

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
        Setting::create([
            'key' => 'default_attendance_radius',
            'value' => '175',
            'type' => 'integer',
        ]);
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
            'attendance_enabled' => true,
            'created_by' => $user->id,
        ]);

        $generatedActivity = Activity::where('agenda_schedule_id', $agendaSchedule->id)->firstOrFail();
        $this->assertSame('2026-06-22', $generatedActivity->activity_date->format('Y-m-d'));
        $this->assertSame('2026-06-22 07:30:00', $generatedActivity->attendance_open_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-22 10:00:00', $generatedActivity->attendance_close_at->format('Y-m-d H:i:s'));
        $this->assertNotNull($generatedActivity->attendance_token);
    }

    public function test_activity_generated_from_schedule_uses_department_chair_when_schedule_has_no_pic(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'Kaderisasi', 'status' => 'active']);
        $chairPosition = Position::create(['name' => 'Ketua Bidang', 'status' => 'active']);
        $memberPosition = Position::create(['name' => 'Anggota', 'status' => 'active']);
        $chair = Member::create([
            'department_id' => $department->id,
            'position_id' => $chairPosition->id,
            'full_name' => 'Ketua Kaderisasi',
            'member_status' => 'active',
        ]);
        Member::create([
            'department_id' => $department->id,
            'position_id' => $memberPosition->id,
            'full_name' => 'Anggota Kaderisasi',
            'member_status' => 'active',
        ]);
        $agendaSchedule = AgendaSchedule::create([
            'department_id' => $department->id,
            'pic_id' => null,
            'title' => 'Latihan Kader',
            'schedule_type' => 'weekly',
            'day_of_week' => 5,
            'start_time' => '19:00',
            'end_time' => '21:00',
            'default_location' => 'Aula Cirengit',
            'default_latitude' => '-6.2100000',
            'default_longitude' => '107.2100000',
            'default_radius' => 140,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('agenda-schedules.activities.store', $agendaSchedule), [
            'activity_date' => '2026-06-26',
        ])->assertRedirect();

        $this->assertDatabaseHas('activities', [
            'agenda_schedule_id' => $agendaSchedule->id,
            'department_id' => $department->id,
            'pic_id' => $chair->id,
            'start_time' => '19:00',
            'end_time' => '21:00',
            'location' => 'Aula Cirengit',
            'attendance_radius' => 140,
        ]);
    }

    public function test_new_activity_uses_automatic_attendance_schedule(): void
    {
        $user = User::factory()->create();
        Setting::insert([
            ['key' => 'default_attendance_radius', 'value' => '180', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_attendance_open_minutes_before', 'value' => '45', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_attendance_close_minutes_after', 'value' => '20', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($user)->post(route('activities.store'), [
            'title' => 'Kajian Default Presensi',
            'activity_date' => '2026-06-20',
            'start_time' => '19:30',
            'end_time' => '21:00',
            'attendance_radius' => 180,
            'status' => 'scheduled',
        ])->assertRedirect();

        $activity = Activity::where('title', 'Kajian Default Presensi')->firstOrFail();

        $this->assertSame(180, $activity->attendance_radius);
        $this->assertSame('2026-06-20 18:45:00', $activity->attendance_open_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-20 21:00:00', $activity->attendance_close_at->format('Y-m-d H:i:s'));
    }

    public function test_edit_activity_recalculates_automatic_attendance_schedule(): void
    {
        $user = User::factory()->create();
        Setting::insert([
            ['key' => 'default_attendance_radius', 'value' => '300', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_attendance_open_minutes_before', 'value' => '90', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_attendance_close_minutes_after', 'value' => '90', 'type' => 'integer', 'created_at' => now(), 'updated_at' => now()],
        ]);
        $activity = Activity::create([
            'title' => 'Kajian Existing',
            'activity_date' => '2026-06-20',
            'start_time' => '19:30',
            'end_time' => '21:00',
            'attendance_radius' => 120,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'attendance_open_at' => '2026-06-20 19:00:00',
            'attendance_close_at' => '2026-06-20 21:30:00',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->put(route('activities.update', $activity), [
            'title' => 'Kajian Existing Updated',
            'activity_date' => '2026-06-20',
            'start_time' => '19:30',
            'end_time' => '21:00',
            'attendance_radius' => 120,
            'status' => 'scheduled',
        ])->assertRedirect(route('activities.show', $activity));

        $activity->refresh();

        $this->assertSame(120, $activity->attendance_radius);
        $this->assertSame('2026-06-20 18:00:00', $activity->attendance_open_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-20 21:00:00', $activity->attendance_close_at->format('Y-m-d H:i:s'));
    }

    public function test_activity_detail_page_shows_control_center_sections(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $pic = Member::create(['full_name' => 'PIC Pendidikan', 'member_status' => 'active']);
        $presentMember = Member::create(['full_name' => 'Anggota Hadir', 'member_status' => 'active']);
        $absentMember = Member::create(['full_name' => 'Anggota Tidak Hadir', 'member_status' => 'active']);
        $activity = Activity::create([
            'department_id' => $department->id,
            'pic_id' => $pic->id,
            'title' => 'Kajian Kontrol',
            'activity_date' => '2026-06-10',
            'start_time' => '20:00',
            'end_time' => '21:30',
            'location' => 'Masjid Cirengit',
            'latitude' => '-6.2000000',
            'longitude' => '107.2000000',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'change_reason' => 'Persiapan kegiatan.',
            'attendance_enabled' => true,
            'attendance_open_at' => '2026-06-10 19:30:00',
            'attendance_close_at' => '2026-06-10 21:45:00',
            'attendance_token' => 'activity-control-token',
            'created_by' => $user->id,
        ]);
        Attendance::create(['activity_id' => $activity->id, 'member_id' => $presentMember->id, 'status' => 'present', 'attendance_method' => 'manual']);
        Attendance::create(['activity_id' => $activity->id, 'member_id' => $absentMember->id, 'status' => 'absent', 'attendance_method' => 'manual']);

        $this->actingAs($user)
            ->get(route('activities.show', $activity))
            ->assertOk()
            ->assertSee('Informasi Kegiatan')
            ->assertSee('Presensi Otomatis')
            ->assertSee('Aksi Cepat')
            ->assertSee('Ringkasan Presensi')
            ->assertSee('10/06/2026')
            ->assertSee('20:00 - 21:30')
            ->assertSee('10/06/2026 19:30')
            ->assertSee('Sinkronkan Peserta')
            ->assertSee('Lihat QR Presensi')
            ->assertSee('Salin Link Presensi')
            ->assertSee('Export Rekap Excel')
            ->assertSee('50.00%');
    }

    public function test_activity_validation_rejects_invalid_required_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('activities.store'), [
            'title' => '',
            'activity_date' => 'invalid-date',
            'attendance_radius' => 0,
            'status' => 'unknown',
        ])->assertSessionHasErrors(['title', 'activity_date', 'attendance_radius', 'status']);
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
