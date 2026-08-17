<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\TotalRevenueWidget;
use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardFiltersDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_state_is_string(): void
    {
        $user = User::factory()->create(['profile' => 'ADMIN']);
        auth()->login($user);

        $widget = new class {
            use \App\Filament\Widgets\Concerns\HasDashboardScope;
        };

        $widget->pageFilters = ['period_range' => '01/08/2026 até 31/08/2026'];

        $this->assertIsString($widget->pageFilters['period_range']);
        $this->assertStringContainsString('01/08/2026', $widget->pageFilters['period_range']);
    }

    public function test_parse_period_range_from_string(): void
    {
        $user = User::factory()->create(['profile' => 'ADMIN']);
        auth()->login($user);

        $widget = new class {
            use \App\Filament\Widgets\Concerns\HasDashboardScope;
        };

        $widget->pageFilters = ['period_range' => '01/08/2026 - 31/08/2026'];

        $ref = new \ReflectionMethod($widget, 'getSelectedPeriod');
        $period = $ref->invoke($widget);

        $this->assertInstanceOf(Carbon::class, $period['start']);
        $this->assertInstanceOf(Carbon::class, $period['end']);
        $this->assertEquals('2026-08-01', $period['start']->format('Y-m-d'));
        $this->assertEquals('2026-08-31', $period['end']->format('Y-m-d'));
    }

    public function test_parse_period_range_from_array(): void
    {
        $user = User::factory()->create(['profile' => 'ADMIN']);
        auth()->login($user);

        $widget = new class {
            use \App\Filament\Widgets\Concerns\HasDashboardScope;
        };

        $widget->pageFilters = ['period_range' => ['start' => '2026-08-01', 'end' => '2026-08-15']];

        $ref = new \ReflectionMethod($widget, 'getSelectedPeriod');
        $period = $ref->invoke($widget);

        $this->assertEquals('2026-08-01', $period['start']->format('Y-m-d'));
        $this->assertEquals('2026-08-15', $period['end']->format('Y-m-d'));
    }

    public function test_fallback_when_no_period(): void
    {
        $user = User::factory()->create(['profile' => 'ADMIN']);
        auth()->login($user);

        $widget = new class {
            use \App\Filament\Widgets\Concerns\HasDashboardScope;
        };

        $widget->pageFilters = [];

        $ref = new \ReflectionMethod($widget, 'getSelectedPeriod');
        $period = $ref->invoke($widget);

        $this->assertEquals(now()->startOfMonth()->format('Y-m-d'), $period['start']->format('Y-m-d'));
        $this->assertEquals(now()->endOfMonth()->format('Y-m-d'), $period['end']->format('Y-m-d'));
    }

    public function test_widget_uses_filtered_period(): void
    {
        $user = User::factory()->create(['profile' => 'ADMIN']);
        auth()->login($user);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'origin' => 'SITE',
        ]);

        // Deal dentro do período selecionado
        Deal::forceCreate([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Dentro',
            'quantity' => 1,
            'total_value' => 1000.00,
            'status' => 'WON',
            'probability' => 100,
            'created_at' => '2026-08-05 10:00:00',
            'updated_at' => '2026-08-05 10:00:00',
        ]);

        // Deal fora do período selecionado
        Deal::forceCreate([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Fora',
            'quantity' => 1,
            'total_value' => 9999.00,
            'status' => 'WON',
            'probability' => 100,
            'created_at' => '2026-09-05 10:00:00',
            'updated_at' => '2026-09-05 10:00:00',
        ]);

        $widget = new TotalRevenueWidget();
        $widget->pageFilters = ['period_range' => '01/08/2026 - 31/08/2026'];

        $ref = new \ReflectionMethod(TotalRevenueWidget::class, 'getMetric');
        $metric = $ref->invoke($widget);

        $this->assertEquals('R$1,000.00', $metric->getFormattedValue());
    }
}
