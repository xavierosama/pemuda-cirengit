<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_page_uses_role_specific_back_link_and_member_information(): void
    {
        $department = Department::create(['name' => 'Pendidikan', 'status' => 'active']);
        $position = Position::create(['name' => 'Anggota', 'status' => 'active']);
        $member = Member::create([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'full_name' => 'Ahmad Anggota',
            'npa' => '20.0001',
            'phone' => '081234567890',
            'member_status' => 'active',
        ]);
        $memberUser = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($memberUser)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Kembali ke Dashboard Anggota')
            ->assertSee('Informasi Anggota')
            ->assertSee('Ahmad Anggota')
            ->assertSee('20.0001')
            ->assertSee('Pendidikan')
            ->assertSee('Anggota');

        $this->actingAs($admin)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Kembali ke Dashboard Admin');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
