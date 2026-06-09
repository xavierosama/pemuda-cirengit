<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_departments(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('departments.store'), [
                'name' => 'Bidang Riset',
                'description' => 'Mengelola kajian dan pengembangan kader.',
                'status' => 'active',
            ])
            ->assertRedirect(route('departments.index'));

        $department = Department::where('name', 'Bidang Riset')->firstOrFail();

        $this->actingAs($user)
            ->get(route('departments.index', ['search' => 'Riset', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Bidang Riset');

        $this->actingAs($user)
            ->get(route('departments.show', $department))
            ->assertOk()
            ->assertSee('Bidang Riset');

        $this->actingAs($user)
            ->put(route('departments.update', $department), [
                'name' => 'Bidang Riset Updated',
                'description' => 'Deskripsi diperbarui.',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Bidang Riset Updated',
            'status' => 'inactive',
        ]);

        $this->actingAs($user)
            ->delete(route('departments.destroy', $department))
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseMissing('departments', [
            'id' => $department->id,
        ]);
    }

    public function test_department_name_must_be_unique(): void
    {
        $user = User::factory()->create();

        Department::create([
            'name' => 'Dakwah',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('departments.store'), [
                'name' => 'Dakwah',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('name');
    }
}
