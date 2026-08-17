<?php

namespace Tests\Feature;

use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_render_reports_page(): void
    {
        $user = User::factory()->create([
            'profile' => UserProfile::ADMIN,
        ]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
        $response->assertSee('Relatórios Gerenciais');
        $response->assertSee('Vendas');
        $response->assertSee('Produtos', false);
    }

    public function test_seller_performance_widget_only_includes_users_with_user_profile(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'profile' => UserProfile::ADMIN,
        ]);

        $manager = User::factory()->create([
            'name' => 'Manager User',
            'profile' => UserProfile::MANAGER,
        ]);

        $seller = User::factory()->create([
            'name' => 'Seller User',
            'profile' => UserProfile::USER,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\Reports\SellerPerformanceWidget::class)
            ->assertSee('Seller User')
            ->assertDontSee('Admin User')
            ->assertDontSee('Manager User');
    }

    public function test_reports_page_mounts_with_current_month_period(): void
    {
        $admin = User::factory()->create([
            'profile' => UserProfile::ADMIN,
        ]);

        $start = \Illuminate\Support\Carbon::now()->startOfMonth()->format('d/m/Y');
        $end = \Illuminate\Support\Carbon::now()->endOfMonth()->format('d/m/Y');
        $expectedRange = "{$start} até {$end}";

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\ReportsPage::class)
            ->assertSet('filters.period_range', $expectedRange);
    }

    public function test_reports_page_filtering_by_user_and_status(): void
    {
        $admin = User::factory()->create([
            'profile' => UserProfile::ADMIN,
        ]);

        $seller = User::factory()->create([
            'name' => 'Vendedor 1',
            'profile' => UserProfile::USER,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\ReportsPage::class)
            ->set('filters.user_id', $seller->id)
            ->set('filters.status', \App\Enums\DealStatus::WON->value)
            ->assertSet('filters.user_id', $seller->id)
            ->assertSet('filters.status', \App\Enums\DealStatus::WON->value);
    }
}
