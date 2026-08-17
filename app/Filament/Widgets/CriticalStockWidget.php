<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Product;
use LaBoiteACode\FilamentDashboardWidgets\Data\Metric;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\MetricWidget;

class CriticalStockWidget extends MetricWidget
{
    use HasDashboardScope;

    protected function getMetric(): Metric
    {
        $period = $this->getSelectedPeriod();

        $baseQuery = fn () => Product::whereColumn('current_stock', '<=', 'minimum_stock')
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q));

        $criticalCount = $baseQuery()->count();

        // Opcional: Adicionar sparkline de produtos críticos por dia no período, se desejar
        $sparklineData = $baseQuery()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('total')
            ->toArray();

        return Metric::make('Estoque Crítico', $criticalCount)
            ->description('Itens precisando de reposição no período')
            ->trend($this->calculateStockTrend())
            ->lowerIsBetter() // Inverte a semântica: menos itens críticos é melhor
            ->icon('heroicon-o-exclamation-triangle')
            ->sparkline($sparklineData)
            ->color('danger');
    }

    /**
     * Trend comparando produtos críticos criados no período selecionado vs anterior.
     */
    protected function calculateStockTrend(): ?float
    {
        $period = $this->getSelectedPeriod();
        $prev = $this->getPreviousPeriod($period);

        $current = Product::whereColumn('current_stock', '<=', 'minimum_stock')
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->count();

        $previous = Product::whereColumn('current_stock', '<=', 'minimum_stock')
            ->whereBetween('created_at', [$prev['start'], $prev['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->count();

        return $this->calculateTrend($current, $previous);
    }
}
