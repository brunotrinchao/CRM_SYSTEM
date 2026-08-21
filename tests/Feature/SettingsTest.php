<?php

namespace Tests\Feature;

use App\Enums\UserProfile;
use App\Filament\Pages\SettingsPage;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_settings_page(): void
    {
        $admin = User::factory()->create([
            'profile' => UserProfile::ADMIN,
            'active' => true,
        ]);

        $this->actingAs($admin)
            ->get(SettingsPage::getUrl())
            ->assertSuccessful();
    }

    public function test_non_admin_cannot_access_settings_page(): void
    {
        $user = User::factory()->create([
            'profile' => UserProfile::USER,
            'active' => true,
        ]);

        $this->actingAs($user)
            ->get(SettingsPage::getUrl())
            ->assertForbidden();
    }

    public function test_admin_can_save_company_name(): void
    {
        $admin = User::factory()->create([
            'profile' => UserProfile::ADMIN,
            'active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(SettingsPage::class)
            ->fillForm([
                'company_name' => 'Empresa Modificada S.A.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('Empresa Modificada S.A.', SystemSetting::getCompanyName());
    }
}
