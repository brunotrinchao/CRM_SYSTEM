<?php

namespace Tests\Feature;

use App\Enums\UserProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_access_panel(): void
    {
        $user = User::factory()->create([
            'profile' => UserProfile::USER,
            'active' => true,
        ]);

        $panel = Filament::getCurrentPanel() ?? Filament::getPanel('admin');

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_deactivated_user_cannot_access_panel(): void
    {
        $user = User::factory()->create([
            'profile' => UserProfile::USER,
            'active' => false,
        ]);

        $panel = Filament::getCurrentPanel() ?? Filament::getPanel('admin');

        $this->assertFalse($user->canAccessPanel($panel));
    }

    public function test_admin_can_deactivate_another_user(): void
    {
        $admin = User::factory()->create(['profile' => UserProfile::ADMIN, 'active' => true]);
        $seller = User::factory()->create(['profile' => UserProfile::USER, 'active' => true]);

        $this->actingAs($admin);

        $seller->update(['active' => false]);

        $this->assertFalse($seller->fresh()->active);
        $panel = Filament::getPanel('admin');
        $this->assertFalse($seller->canAccessPanel($panel));
    }
}
