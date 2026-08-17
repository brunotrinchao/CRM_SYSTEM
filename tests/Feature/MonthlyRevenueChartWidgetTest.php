<?php

namespace Tests\Feature;

use App\Filament\Widgets\Reports\MonthlyRevenueChartWidget;

class ConcreteMonthlyRevenueChartWidget extends MonthlyRevenueChartWidget
{
    public ?array $pageFilters = [];
}

class MonthlyRevenueChartWidgetTest extends \Tests\TestCase
{
    public function test_heading_changes_dynamically_based_on_period(): void
    {
        $widget = new ConcreteMonthlyRevenueChartWidget();

        // Período curto (< 30 dias) no mesmo mês
        $widget->pageFilters = [
            'period_range' => '01/08/2026 até 15/08/2026',
        ];
        $this->assertStringContainsString('Faturamento de Agosto de 2026', $widget->getHeading());

        // Período longo (>= 30 dias)
        $widget->pageFilters = [
            'period_range' => '01/05/2026 até 31/08/2026',
        ];
        $this->assertStringContainsString('Faturamento por Mês', $widget->getHeading());
    }
}
