<?php

namespace App\Filament\Widgets;

use App\Enums\DealStatus;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal;
use LaBoiteACode\FilamentDashboardWidgets\Data\Metric;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\MetricWidget;

class ConversionRateWidget extends MetricWidget
{
    use HasDashboardScope;

    protected function getMetric(): Metric
    {
        $period = $this->getSelectedPeriod();

        $baseQuery = fn () => Deal::query()
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q));

        // Total de negócios do período
        $total = (float) $baseQuery()->count();

        // Negócios ganhos (WON) do período
        $won = (float) $baseQuery()
            ->where('status', DealStatus::WON)
            ->count();

        $rate = $total > 0 ? ($won / $total) * 100 : 0;

        // Calcula a quantidade de dias no período selecionado (incluindo o próprio dia inicial)
        $startDate = \Carbon\Carbon::parse($period['start']);
        $endDate = \Carbon\Carbon::parse($period['end']);
        $daysCount = $startDate->diffInDays($endDate) + 1;

        // Taxa de conversão ou volume de ganhos agrupado por dia para o sparkline
        $sparklineData = $baseQuery()
            ->selectRaw('DATE(created_at) as date, 
                        SUM(CASE WHEN status = \'WON\' THEN 1 ELSE 0 END) as won_count, 
                        COUNT(*) as total_count')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->map(function ($item) {
                return $item->total_count > 0 ? ($item->won_count / $item->total_count) * 100 : 0;
            })
            ->toArray();

            // Arredonda para o número inteiro mais próximo
        $daysCount = round($startDate->diffInDays($endDate) + 1);

        return Metric::make('Taxa de Conversão', $rate)
            ->formatUsing(fn (float $value) => number_format($value, 1, ',', '.') . '%')
            ->description("Nos últimos {$daysCount} dias") // Exibe dinamicamente os dias do filtro
            ->trend($this->calculateRateTrend())
            ->icon('heroicon-o-chart-bar')
            ->sparkline($sparklineData)
            ->color('warning');
    }

    /**
     * Trend (%) da taxa de conversão entre o período selecionado e o anterior.
     */
    protected function calculateRateTrend(): ?float
    {
        $period = $this->getSelectedPeriod();
        $prev = $this->getPreviousPeriod($period);

        $current = $this->rateForPeriod($period['start'], $period['end']);
        $previous = $this->rateForPeriod($prev['start'], $prev['end']);

        if ($current === null || $previous === null) {
            return null;
        }

        return $this->calculateTrend($current, $previous);
    }

    /**
     * Taxa de conversão (%) de um período específico.
     */
    protected function rateForPeriod($start, $end): ?float
    {
        $baseQuery = fn () => Deal::query()
            ->whereBetween('created_at', [$start, $end])
            ->tap(fn ($q) => $this->scopeByProfile($q));

        $total = $baseQuery()->count();

        if ($total === 0) {
            return null;
        }

        $won = $baseQuery()
            ->where('status', DealStatus::WON)
            ->count();

        return ($won / $total) * 100;
    }
}
