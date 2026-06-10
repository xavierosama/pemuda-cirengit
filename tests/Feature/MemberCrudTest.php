<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_members(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $position = Position::create(['name' => 'Anggota', 'status' => 'active']);

        $this->actingAs($user)->post(route('members.store'), [
            'full_name' => 'Ahmad Cirengit',
            'npa' => 'PC-001',
            'phone' => '08123456789',
            'email' => 'ahmad@example.test',
            'address' => 'Cirengit',
            'joined_at' => '2026-06-10',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'member_status' => 'active',
            'notes' => 'Anggota baru.',
        ])->assertRedirect(route('members.index'));

        $member = Member::where('full_name', 'Ahmad Cirengit')->firstOrFail();

        $this->actingAs($user)
            ->get(route('members.index', [
                'search' => 'PC-001',
                'department_id' => $department->id,
                'position_id' => $position->id,
                'member_status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('Ahmad Cirengit')
            ->assertSee('PC-001')
            ->assertSee('Pendidikan')
            ->assertSee('Anggota');

        $this->actingAs($user)
            ->get(route('members.show', $member))
            ->assertOk()
            ->assertSee('Ahmad Cirengit')
            ->assertSee('ahmad@example.test');

        $this->actingAs($user)->put(route('members.update', $member), [
            'full_name' => 'Ahmad Updated',
            'email' => 'updated@example.test',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'member_status' => 'alumni',
        ])->assertRedirect(route('members.index'));

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'full_name' => 'Ahmad Updated',
            'member_status' => 'alumni',
            'npa' => 'PC-001',
        ]);

        $this->actingAs($user)
            ->delete(route('members.destroy', $member))
            ->assertRedirect(route('members.index'));

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
    }

    public function test_member_validation_rejects_invalid_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('members.store'), [
            'full_name' => '',
            'email' => 'bukan-email',
            'joined_at' => 'bukan-tanggal',
            'department_id' => 999,
            'position_id' => 999,
            'member_status' => 'unknown',
        ])->assertSessionHasErrors([
            'full_name',
            'email',
            'joined_at',
            'department_id',
            'position_id',
            'member_status',
        ]);
    }

    public function test_npa_is_nullable_and_unique_when_filled(): void
    {
        $user = User::factory()->create();
        $member = Member::create([
            'full_name' => 'Anggota NPA',
            'npa' => 'NPA-001',
            'member_status' => 'active',
        ]);

        Member::create(['full_name' => 'NPA Kosong 1', 'npa' => null, 'member_status' => 'active']);
        Member::create(['full_name' => 'NPA Kosong 2', 'npa' => null, 'member_status' => 'active']);

        $this->actingAs($user)->post(route('members.store'), [
            'full_name' => 'Duplikat NPA',
            'npa' => 'NPA-001',
            'member_status' => 'active',
        ])->assertSessionHasErrors([
            'npa' => 'NPA sudah digunakan oleh anggota lain.',
        ]);

        $this->actingAs($user)->put(route('members.update', $member), [
            'full_name' => 'Anggota NPA Updated',
            'npa' => 'NPA-001',
            'member_status' => 'active',
        ])->assertRedirect(route('members.index'));
    }

    public function test_npa_unique_constraint_exists_in_database(): void
    {
        Member::create([
            'full_name' => 'Anggota NPA',
            'npa' => 'NPA-UNIK',
            'member_status' => 'active',
        ]);

        $this->expectException(QueryException::class);

        Member::create([
            'full_name' => 'Anggota Duplikat',
            'npa' => 'NPA-UNIK',
            'member_status' => 'active',
        ]);
    }
}
