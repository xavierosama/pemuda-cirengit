<?php

namespace Tests\Feature\Auth;

use App\Models\Member;
use App\Models\User;
use App\Support\DefaultAdminCredentials;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertSee('Username, Email, atau NPA')
            ->assertSee('Masukkan username, email, atau NPA');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_default_seeded_admin_can_authenticate_using_admin_username(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::where('email', DefaultAdminCredentials::EMAIL)->firstOrFail();

        $response = $this->post('/login', [
            'email' => DefaultAdminCredentials::LOGIN,
            'password' => DefaultAdminCredentials::PASSWORD,
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_member_can_authenticate_using_npa(): void
    {
        $member = Member::create([
            'full_name' => 'Anggota NPA',
            'npa' => '12.3456',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'member',
        ]);

        $response = $this->post('/login', [
            'email' => $member->npa,
            'password' => 'password',
            'remember' => 'on',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('member.home', absolute: false));
    }

    public function test_internal_user_can_authenticate_using_npa_and_redirects_to_dashboard(): void
    {
        $member = Member::create([
            'full_name' => 'Pengurus NPA',
            'npa' => '12.9999',
            'member_status' => 'active',
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => 'secretary',
        ]);

        $response = $this->post('/login', [
            'email' => $member->npa,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_npa_without_user_account_shows_clear_login_error(): void
    {
        $member = Member::create([
            'full_name' => 'Anggota Tanpa Akun',
            'npa' => '99.0001',
            'member_status' => 'active',
        ]);

        $this->post('/login', [
            'email' => $member->npa,
            'password' => 'password',
        ])->assertSessionHasErrors([
            'email' => 'NPA ditemukan, tetapi belum memiliki akun login. Silakan hubungi pengurus.',
        ]);

        $this->assertGuest();
    }

    public function test_unknown_npa_uses_general_login_error(): void
    {
        $this->post('/login', [
            'email' => '00.0000',
            'password' => 'password',
        ])->assertSessionHasErrors([
            'email' => trans('auth.failed'),
        ]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
