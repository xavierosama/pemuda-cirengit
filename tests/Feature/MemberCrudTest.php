<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
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
            'member_number' => 'PC-001',
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
                'search' => '081234',
                'department_id' => $department->id,
                'position_id' => $position->id,
                'member_status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('Ahmad Cirengit')
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
}
