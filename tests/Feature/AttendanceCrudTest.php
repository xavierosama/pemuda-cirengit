<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ZipArchive;
use Tests\TestCase;

class AttendanceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_attendance_updates_existing_record_without_duplicate(): void
    {
        $user = User::factory()->create();
        $activity = $this->createActivity($user);
        $member = Member::create(['full_name' => 'Ahmad', 'member_status' => 'active']);

        $payload = [
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'present',
            'notes' => 'Datang tepat waktu.',
        ];

        $this->actingAs($user)
            ->post(route('activities.attendances.store', $activity), $payload)
            ->assertRedirect(route('activities.attendances.index', $activity));

        $this->actingAs($user)
            ->post(route('activities.attendances.store', $activity), array_merge($payload, [
                'status' => 'permission',
                'notes' => 'Diperbarui menjadi izin.',
            ]))
            ->assertRedirect(route('activities.attendances.index', $activity));

        $this->assertSame(1, Attendance::count());
        $this->assertDatabaseHas('attendances', [
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'permission',
            'attendance_method' => 'manual',
            'created_by' => $user->id,
        ]);
    }

    public function test_bulk_attendance_inserts_and_updates_active_members(): void
    {
        $user = User::factory()->create();
        $activity = $this->createActivity($user);
        $firstMember = Member::create(['full_name' => 'Anggota Satu', 'member_status' => 'active']);
        $secondMember = Member::create(['full_name' => 'Anggota Dua', 'member_status' => 'active']);
        Member::create(['full_name' => 'Anggota Nonaktif', 'member_status' => 'inactive']);

        $payload = [
            'activity_id' => $activity->id,
            'attendances' => [
                ['member_id' => $firstMember->id, 'status' => 'present', 'notes' => null],
                ['member_id' => $secondMember->id, 'status' => 'absent', 'notes' => 'Tidak hadir.'],
            ],
        ];

        $this->actingAs($user)
            ->put(route('activities.attendances.bulk.store', $activity), $payload)
            ->assertRedirect(route('activities.attendances.index', $activity));

        $payload['attendances'][0]['status'] = 'need_verification';

        $this->actingAs($user)
            ->put(route('activities.attendances.bulk.store', $activity), $payload)
            ->assertRedirect(route('activities.attendances.index', $activity));

        $this->assertSame(2, Attendance::count());
        $this->assertDatabaseHas('attendances', [
            'activity_id' => $activity->id,
            'member_id' => $firstMember->id,
            'status' => 'need_verification',
        ]);

        $this->actingAs($user)
            ->get(route('activities.attendances.index', $activity))
            ->assertOk()
            ->assertSee('Anggota Satu')
            ->assertSee('Anggota Dua');
    }

    public function test_attendance_index_filters_and_records_can_be_edited_and_deleted(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $activity = $this->createActivity($user, [
            'department_id' => $department->id,
            'title' => 'Kajian Pendidikan',
            'start_time' => '20:00',
            'end_time' => '21:00',
            'location' => 'Masjid Cirengit',
        ]);
        $member = Member::create([
            'department_id' => $department->id,
            'full_name' => 'Budi Pendidikan',
            'member_status' => 'active',
        ]);
        $attendance = Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $member->id,
            'status' => 'present',
            'attendance_method' => 'manual',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('attendances.index', [
                'search' => 'Kajian',
                'department_id' => $department->id,
                'activity_status' => 'scheduled',
                'attendance_enabled' => '1',
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
            ]))
            ->assertOk()
            ->assertSee('Kelola daftar hadir kegiatan, status presensi, dan verifikasi kehadiran anggota.')
            ->assertSee('Total Kegiatan dengan Presensi Aktif')
            ->assertSee('Filter Daftar Hadir')
            ->assertSee('Tabel Daftar Hadir')
            ->assertSee('Kajian Pendidikan')
            ->assertSee('25/06/2026')
            ->assertSee('20:00 - 21:00')
            ->assertSee('Pendidikan')
            ->assertSee('Buka Daftar Hadir')
            ->assertSee('QR Presensi')
            ->assertSee('Sinkronkan Peserta')
            ->assertSee('Export Excel');

        $this->actingAs($user)
            ->put(route('attendances.update', $attendance), [
                'status' => 'permission',
                'notes' => 'Izin resmi.',
            ])
            ->assertRedirect(route('activities.attendances.index', $activity));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'permission',
            'notes' => 'Izin resmi.',
        ]);

        $this->actingAs($user)
            ->delete(route('attendances.destroy', $attendance))
            ->assertRedirect(route('activities.attendances.index', $activity));

        $this->assertDatabaseMissing('attendances', ['id' => $attendance->id]);
    }

    public function test_manual_attendance_validation_requires_unique_activity_member_context(): void
    {
        $user = User::factory()->create();
        $activity = $this->createActivity($user);

        $this->actingAs($user)
            ->post(route('activities.attendances.store', $activity), [
                'activity_id' => 999,
                'member_id' => 999,
                'status' => 'unknown',
            ])
            ->assertSessionHasErrors(['activity_id', 'member_id', 'status']);
    }

    public function test_attendance_participants_can_be_synced_without_changing_existing_records(): void
    {
        $user = User::factory()->create(['role' => 'secretary']);
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $otherDepartment = Department::create(['name' => 'Dakwah', 'status' => 'active']);
        $activity = $this->createActivity($user, ['department_id' => $department->id]);
        $presentMember = Member::create([
            'department_id' => $department->id,
            'full_name' => 'Anggota Hadir',
            'member_status' => 'active',
        ]);
        $newMember = Member::create([
            'department_id' => $department->id,
            'full_name' => 'Anggota Belum Ada',
            'member_status' => 'active',
        ]);
        Member::create([
            'department_id' => $otherDepartment->id,
            'full_name' => 'Anggota Bidang Lain',
            'member_status' => 'active',
        ]);
        Member::create([
            'department_id' => $department->id,
            'full_name' => 'Anggota Nonaktif',
            'member_status' => 'inactive',
        ]);
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $presentMember->id,
            'status' => 'present',
            'attendance_method' => 'link',
            'verification_status' => 'valid',
            'checked_in_at' => '2026-06-25 10:00:00',
        ]);

        $this->actingAs($user)
            ->post(route('activities.attendances.sync-participants', $activity))
            ->assertRedirect(route('activities.attendances.index', $activity))
            ->assertSessionHas('success', 'Sinkronisasi peserta selesai. 1 anggota baru ditambahkan ke daftar hadir, 1 anggota sudah ada sebelumnya.');

        $this->assertSame(2, Attendance::where('activity_id', $activity->id)->count());
        $this->assertDatabaseHas('attendances', [
            'activity_id' => $activity->id,
            'member_id' => $presentMember->id,
            'status' => 'present',
            'attendance_method' => 'link',
        ]);
        $this->assertDatabaseHas('attendances', [
            'activity_id' => $activity->id,
            'member_id' => $newMember->id,
            'status' => 'absent',
            'attendance_method' => 'manual',
            'verification_status' => 'valid',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('activities.attendances.sync-participants', $activity))
            ->assertSessionHas('success', 'Sinkronisasi peserta selesai. 0 anggota baru ditambahkan ke daftar hadir, 2 anggota sudah ada sebelumnya.');

        $attendance = Attendance::where('activity_id', $activity->id)->where('member_id', $newMember->id)->firstOrFail();

        $this->actingAs($user)
            ->put(route('attendances.update', $attendance), [
                'status' => 'permission',
                'notes' => 'Izin setelah sinkronisasi.',
            ])
            ->assertRedirect(route('activities.attendances.index', $activity));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'permission',
            'notes' => 'Izin setelah sinkronisasi.',
        ]);
    }

    public function test_sync_button_is_visible_on_activity_attendance_page(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $activity = $this->createActivity($user, ['attendance_token' => 'sync-visible-token']);

        $this->actingAs($user)
            ->get(route('activities.attendances.index', $activity))
            ->assertOk()
            ->assertSee('Persentase Kehadiran')
            ->assertSee('Cari nama anggota atau NPA')
            ->assertSee('Sinkronkan Peserta Presensi')
            ->assertSee('Lihat QR Presensi')
            ->assertSee('Salin Link Presensi')
            ->assertSee('Export Excel');
    }

    public function test_activity_attendance_page_filters_by_search_status_and_department(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $education = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $dakwah = Department::create(['name' => 'Dakwah', 'status' => 'active']);
        $position = Position::create(['name' => 'Anggota', 'status' => 'active']);
        $activity = $this->createActivity($user, [
            'title' => 'Kajian Filter',
            'activity_date' => '2026-06-10',
            'start_time' => '20:00',
            'end_time' => '21:00',
            'location' => 'Masjid Cirengit',
            'attendance_token' => 'filter-token',
        ]);
        $included = Member::create([
            'department_id' => $education->id,
            'position_id' => $position->id,
            'npa' => '20.0001',
            'full_name' => 'Ahmad Pendidikan',
            'member_status' => 'active',
        ]);
        $excluded = Member::create([
            'department_id' => $dakwah->id,
            'position_id' => $position->id,
            'npa' => '30.0001',
            'full_name' => 'Budi Dakwah',
            'member_status' => 'active',
        ]);
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $included->id,
            'status' => 'present',
            'attendance_method' => 'link',
            'checked_in_at' => '2026-06-10 20:05:00',
            'distance_from_activity' => 10,
            'verification_status' => 'valid',
            'notes' => 'Datang.',
        ]);
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $excluded->id,
            'status' => 'absent',
            'attendance_method' => 'manual',
            'verification_status' => 'valid',
        ]);

        $this->actingAs($user)
            ->get(route('activities.attendances.index', [
                'activity' => $activity,
                'search' => '20.0001',
                'status' => 'present',
                'department_id' => $education->id,
            ]))
            ->assertOk()
            ->assertSee('Kajian Filter')
            ->assertSee('10/06/2026')
            ->assertSee('20:00 - 21:00')
            ->assertSee('Ahmad Pendidikan')
            ->assertSee('20.0001')
            ->assertDontSee('Budi Dakwah')
            ->assertSee('10/06/2026 20:05')
            ->assertSee('Verifikasi')
            ->assertSee('Ubah Status');
    }

    public function test_internal_user_can_export_activity_attendance_recap_to_excel(): void
    {
        $user = User::factory()->create(['role' => 'secretary']);
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $position = Position::create(['name' => 'Anggota', 'status' => 'active']);
        $pic = Member::create([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'full_name' => 'Ustadz PIC',
            'member_status' => 'active',
        ]);
        $activity = $this->createActivity($user, [
            'department_id' => $department->id,
            'pic_id' => $pic->id,
            'title' => 'Kajian Rutin Pemuda',
            'activity_date' => '2026-06-10',
            'start_time' => '20:00',
            'end_time' => '21:30',
            'location' => 'Masjid Cirengit',
        ]);
        $firstMember = Member::create([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'npa' => '20.0001',
            'full_name' => 'Ahmad Hadir',
            'member_status' => 'active',
        ]);
        $secondMember = Member::create([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'npa' => '20.0002',
            'full_name' => 'Budi Izin',
            'member_status' => 'active',
        ]);
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $firstMember->id,
            'status' => 'present',
            'attendance_method' => 'link',
            'checked_in_at' => '2026-06-10 20:05:00',
            'distance_from_activity' => 12.5,
            'verification_status' => 'valid',
            'notes' => 'Tepat waktu',
        ]);
        Attendance::create([
            'activity_id' => $activity->id,
            'member_id' => $secondMember->id,
            'status' => 'permission',
            'attendance_method' => 'manual',
            'verification_status' => 'valid',
            'notes' => 'Izin keluarga',
        ]);

        $response = $this->actingAs($user)
            ->get(route('activities.attendances.export', $activity));

        $response->assertOk()
            ->assertDownload('rekap-presensi-kajian-rutin-pemuda-2026-06-10.xlsx');

        $worksheet = $this->worksheetXmlFromDownload($response);

        foreach ([
            'Nama kegiatan', 'Kajian Rutin Pemuda', 'Tanggal kegiatan', '10/06/2026',
            'Waktu kegiatan', '20:00 - 21:30', 'Lokasi kegiatan', 'Masjid Cirengit',
            'Bidang', 'Pendidikan', 'PIC kegiatan', 'Ustadz PIC',
            'Total Hadir', 'Total Izin', 'Persentase Kehadiran', '50.00%',
            'No', 'NPA', 'Nama Anggota', 'Jabatan', 'Status Kehadiran',
            'Metode Presensi', 'Waktu Presensi', 'Jarak dari Lokasi Kegiatan',
            'Status Verifikasi', 'Catatan', '20.0001', 'Ahmad Hadir',
            'Hadir', 'Link', '10/06/2026 20:05', '12.50 m', 'Tepat waktu',
            '20.0002', 'Budi Izin', 'Izin', 'Manual', 'Izin keluarga',
        ] as $value) {
            $this->assertStringContainsString($value, $worksheet);
        }
    }

    public function test_member_role_cannot_export_activity_attendance_recap(): void
    {
        $member = Member::create(['full_name' => 'Anggota', 'member_status' => 'active']);
        $user = User::factory()->create(['role' => 'member', 'member_id' => $member->id]);
        $activity = $this->createActivity(User::factory()->create(['role' => 'admin']));

        $this->actingAs($user)
            ->get(route('activities.attendances.export', $activity))
            ->assertRedirect(route('member.home'))
            ->assertSessionHas('warning', 'Dashboard admin hanya dapat diakses oleh pengurus.');
    }

    private function createActivity(User $user, array $overrides = []): Activity
    {
        return Activity::create(array_merge([
            'title' => 'Kajian Kehadiran',
            'activity_date' => '2026-06-25',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'created_by' => $user->id,
        ], $overrides));
    }

    private function worksheetXmlFromDownload($response): string
    {
        $zip = new ZipArchive();
        $zip->open($response->baseResponse->getFile()->getPathname());
        $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        return $worksheet;
    }
}
