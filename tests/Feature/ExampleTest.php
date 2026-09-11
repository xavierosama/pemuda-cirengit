<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_homepage_displays_public_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Kajian')
            ->assertSee('Login Member');
    }

    public function test_internal_user_homepage_displays_public_landing_with_dashboard_link(): void
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/');

        $response->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_member_homepage_displays_public_landing_with_dashboard_link(): void
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'member']))
            ->get('/');

        $response->assertOk()
            ->assertSee('Dashboard');
    }
}
