<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_homepage_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_internal_user_homepage_redirects_to_dashboard(): void
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_member_homepage_redirects_to_member_page(): void
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'member']))
            ->get('/');

        $response->assertRedirect(route('member.home'));
    }
}
