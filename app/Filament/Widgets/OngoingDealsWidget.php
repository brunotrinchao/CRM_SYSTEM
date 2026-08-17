<?php

namespace App\Filament\Widgets;

use App\Enums\DealStatus;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal;
use LaBoiteACode\FilamentDashboardWidgets\Data\Metric;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\MetricWidget;

class OngoingDealsWidget extends MetricWidget
{
    use HasDashboardScope;

    protected function getMetric(): Metric
    {
        $period = $this->getSelectedPeriod();

        $baseQuery = fn () => Deal::query()
            ->whereIn('status', [DealStatus::PENDING, DealStatus::NEGOTIATING])
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q));

            // Faturamento do período selecionado
        $count = (float) $baseQuery()->count();

            // Faturamento total agrupado por dia no período selecionado
        $totalsByDay = $baseQuery()
            ->selectRaw('DATE(created_at) as date, SUM(total_value) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('total') // Pega apenas a coluna 'total'
            ->toArray();   // Transforma em um array PHP simples

        return Metric::make('Negócios em Andamento', $count)
            ->description('Ativos no pipeline atual')
            ->trend($this->calculateTrendForOngoing())
            ->icon('heroicon-o-briefcase')
            ->sparkline($totalsByDay)
            ->color('info');
    }

    /**
     * Trend comparando negócios ativos do período selecionado com o período anterior.
     */
    protected function calculateTrendForOngoing(): ?float
    {
        $period = $this->getSelectedPeriod();
        $prev = $this->getPreviousPeriod($period);

        $current = Deal::whereIn('status', [DealStatus::PENDING, DealStatus::NEGOTIATING])
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->count();

        $previous = Deal::whereIn('status', [DealStatus::PENDING, DealStatus::NEGOTIATING])
            ->whereBetween('created_at', [$prev['start'], $prev['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->count();

        return $this->calculateTrend($current, $previous);
    }
}
