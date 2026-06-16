<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminTableControlsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

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

    public function test_activity_index_default_sort_shows_nearest_activities_first(): void
    {
        Carbon::setTestNow('2026-06-16 09:00:00');
        $admin = User::factory()->create();

        Activity::create(['title' => 'Lewat Lama', 'activity_date' => '2026-06-10', 'start_time' => '20:00', 'attendance_radius' => 100, 'status' => 'scheduled']);
        Activity::create(['title' => 'Mendatang Jauh', 'activity_date' => '2026-06-23', 'start_time' => '20:00', 'attendance_radius' => 100, 'status' => 'scheduled']);
        Activity::create(['title' => 'Lewat Kemarin', 'activity_date' => '2026-06-15', 'start_time' => '21:00', 'attendance_radius' => 100, 'status' => 'scheduled']);
        Activity::create(['title' => 'Hari Ini Malam', 'activity_date' => '2026-06-16', 'start_time' => '20:00', 'attendance_radius' => 100, 'status' => 'scheduled']);
        Activity::create(['title' => 'Besok', 'activity_date' => '2026-06-17', 'start_time' => '19:30', 'attendance_radius' => 100, 'status' => 'scheduled']);
        Activity::create(['title' => 'Hari Ini Sore', 'activity_date' => '2026-06-16', 'start_time' => '16:00', 'attendance_radius' => 100, 'status' => 'scheduled']);

        $this->actingAs($admin)
            ->get(route('activities.index'))
            ->assertOk()
            ->assertViewHas('currentSort', null)
            ->assertViewHas('activities', function ($activities) {
                return $activities->pluck('title')->all() === [
                    'Hari Ini Sore',
                    'Hari Ini Malam',
                    'Besok',
                    'Mendatang Jauh',
                    'Lewat Kemarin',
                    'Lewat Lama',
                ];
            });
    }

    public function test_activity_index_manual_sort_overrides_nearest_default_sort(): void
    {
        Carbon::setTestNow('2026-06-16 09:00:00');
        $admin = User::factory()->create();

        Activity::create(['title' => 'Zeta Hari Ini', 'activity_date' => '2026-06-16', 'start_time' => '20:00', 'attendance_radius' => 100, 'status' => 'scheduled']);
        Activity::create(['title' => 'Alpha Mendatang', 'activity_date' => '2026-06-23', 'start_time' => '20:00', 'attendance_radius' => 100, 'status' => 'scheduled']);

        $this->actingAs($admin)
            ->get(route('activities.index', ['sort' => 'title', 'direction' => 'asc']))
            ->assertOk()
            ->assertViewHas('currentSort', 'title')
            ->assertViewHas('activities', fn ($activities) => $activities->first()->title === 'Alpha Mendatang');
    }
}
