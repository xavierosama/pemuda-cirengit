<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $activity = $this->createActivity($user);
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
                'activity_id' => $activity->id,
                'status' => 'present',
                'member_id' => $member->id,
                'department_id' => $department->id,
            ]))
            ->assertOk()
            ->assertSee('Budi Pendidikan')
            ->assertSee('Pendidikan');

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

    private function createActivity(User $user): Activity
    {
        return Activity::create([
            'title' => 'Kajian Kehadiran',
            'activity_date' => '2026-06-25',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'attendance_enabled' => true,
            'created_by' => $user->id,
        ]);
    }
}
