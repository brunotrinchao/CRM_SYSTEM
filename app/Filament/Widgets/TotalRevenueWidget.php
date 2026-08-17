<?php

namespace App\Filament\Widgets;

use App\Enums\DealStatus;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal;
use Illuminate\Support\Number;
use LaBoiteACode\FilamentDashboardWidgets\Data\Metric;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\MetricWidget;

class TotalRevenueWidget extends MetricWidget
{
    use HasDashboardScope;

    protected function getMetric(): Metric
    {
        // Período selecionado no dashboard (fallback: mês atual)
        $period = $this->getSelectedPeriod();

        // Base da query: apenas negócios ganhos (WON) no período, escopado por perfil.
        // Faturamento = soma do total_value dos deals WON, consistente com
        // o RevenueEvolutionWidget e o funil (status "Ganho").
        $baseQuery = fn () => Deal::query()
            ->where('status', DealStatus::WON)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q));

        // Faturamento do período selecionado
        $total = (float) $baseQuery()->sum('total_value');

        // Faturamento total agrupado por dia no período selecionado
        $totalsByDay = $baseQuery()
            ->selectRaw('DATE(created_at) as date, SUM(total_value) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('total') // Pega apenas a coluna 'total'
            ->toArray();   // Transforma em um array PHP simples

        // Faturamento do período anterior (mesma duração)
        $prev = $this->getPreviousPeriod($period);

        $previousTotal = (float) Deal::query()
            ->where('status', DealStatus::WON)
            ->whereBetween('created_at', [$prev['start'], $prev['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->sum('total_value');

        return Metric::make('Faturamento Total', $total)
            ->formatUsing(fn (float $value) => Number::currency($value, 'BRL'))
            ->description('Comparado ao período anterior')
            ->trend($this->calculateTrend($total, $previousTotal))
            ->icon('heroicon-o-currency-dollar')
            ->sparkline($totalsByDay)
            ->color('success');
    }
}
