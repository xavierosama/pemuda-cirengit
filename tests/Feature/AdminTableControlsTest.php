<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTableControlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_table_sorts_with_allowlisted_column_and_uses_per_page(): void
    {
        $admin = User::factory()->create();
        Member::create(['full_name' => 'Zaki', 'npa' => '003', 'email' => 'zaki@example.test', 'member_status' => 'active']);
        Member::create(['full_name' => 'Ahmad', 'npa' => '001', 'email' => 'ahmad@example.test', 'member_status' => 'active']);
        Member::create(['full_name' => 'Budi', 'npa' => '002', 'email' => 'budi@example.test', 'member_status' => 'inactive']);

        $this->actingAs($admin)
            ->get(route('members.index', [
                'sort' => 'full_name',
                'direction' => 'asc',
                'per_page' => 25,
            ]))
            ->assertOk()
            ->assertViewHas('perPage', 25)
            ->assertViewHas('currentSort', 'full_name')
            ->assertViewHas('members', fn ($members) => $members->first()->full_name === 'Ahmad');
    }

    public function test_invalid_sort_and_per_page_fall_back_to_existing_defaults(): void
    {
        $admin = User::factory()->create();
        $older = Department::create(['name' => 'Older', 'status' => 'active']);
        $newer = Department::create(['name' => 'Newer', 'status' => 'active']);
        $older->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->save();
        $newer->forceFill(['created_at' => now(), 'updated_at' => now()])->save();

        $this->actingAs($admin)
            ->get(route('departments.index', [
                'sort' => 'name;drop-table',
                'direction' => 'sideways',
                'per_page' => 999,
            ]))
            ->assertOk()
            ->assertViewHas('perPage', 10)
            ->assertViewHas('currentSort', null)
            ->assertViewHas('departments', fn ($departments) => $departments->first()->is($newer)
                && ! $departments->first()->is($older));
    }

    public function test_activity_attendance_table_sorts_collection_rows(): void
    {
        $admin = User::factory()->create();
        $activity = Activity::create([
            'title' => 'Kajian Sort',
            'activity_date' => '2026-06-20',
            'attendance_radius' => 100,
            'status' => 'scheduled',
            'created_by' => $admin->id,
        ]);
        $zaki = Member::create(['full_name' => 'Zaki', 'npa' => '003', 'member_status' => 'active']);
        $ahmad = Member::create(['full_name' => 'Ahmad', 'npa' => '001', 'member_status' => 'active']);

        Attendance::create(['activity_id' => $activity->id, 'member_id' => $zaki->id, 'status' => 'present', 'attendance_method' => 'manual']);
        Attendance::create(['activity_id' => $activity->id, 'member_id' => $ahmad->id, 'status' => 'absent', 'attendance_method' => 'manual']);

        $this->actingAs($admin)
            ->get(route('activities.attendances.index', $activity, [
                'sort' => 'full_name',
                'direction' => 'asc',
                'per_page' => 10,
            ]))
            ->assertOk()
            ->assertViewHas('attendances', fn ($attendances) => $attendances->first()->member->is($ahmad));
    }
}
