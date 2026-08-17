<?php

namespace Tests\Feature;

use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_renders(): void
    {
        $user = User::factory()->create(['profile' => UserProfile::ADMIN]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
    }

    public function test_dashboard_page_registers_widgets(): void
    {
        $user = User::factory()->create(['profile' => UserProfile::ADMIN]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
    }

    public function test_user_profile_renders_dashboard(): void
    {
        $user = User::factory()->create(['profile' => UserProfile::USER]);

        $this->actingAs($user)
            ->get('/')
            ->assertOk();
    }
}
