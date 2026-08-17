<?php

namespace App\Filament\Widgets\Reports;

use App\Enums\DealStatus;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class SalesOverviewStatsWidget extends BaseWidget
{
    use HasDashboardScope;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $period = $this->getSelectedPeriod();

        $baseQuery = fn () => Deal::query()
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->tap(fn ($q) => $this->scopeByStatus($q));

        // 1. Faturamento no Período (Apenas WON)
        $revenue = (float) $baseQuery()
            ->where('status', DealStatus::WON)
            ->sum('total_value');

        // 2. Taxa de Conversão
        $totalDeals = $baseQuery()->count();
        $wonDeals = $baseQuery()->where('status', DealStatus::WON)->count();
        $conversionRate = $totalDeals > 0 ? ($wonDeals / $totalDeals) * 100 : 0;

        // 3. Tempo Médio de Ciclo (em dias) para negócios fechados no período
        $closedDeals = $baseQuery()
            ->where('status', DealStatus::WON)
            ->whereNotNull('actual_close_date')
            ->get(['created_at', 'actual_close_date']);

        $avgCycleDays = 0;
        if ($closedDeals->count() > 0) {
            $totalDays = $closedDeals->sum(function ($deal) {
                $created = Carbon::parse($deal->created_at);
                $closed = Carbon::parse($deal->actual_close_date);
                return max(0, $created->diffInDays($closed));
            });
            $avgCycleDays = round($totalDays / $closedDeals->count(), 1);
        }

        return [
            Stat::make('Faturamento no Período', Number::currency($revenue, 'BRL'))
                ->description('Total de negócios ganhos')
                ->descriptionIcon(Phosphor::CurrencyDollarDuotone)
                ->color('success'),

            Stat::make('Taxa de Conversão', number_format($conversionRate, 1, ',', '.') . '%')
                ->description("{$wonDeals} de {$totalDeals} negócios criados")
                ->descriptionIcon(Phosphor::TrendUpDuotone)
                ->color('info'),

            Stat::make('Tempo Médio de Ciclo', "{$avgCycleDays} dias")
                ->description('Da criação ao fechamento real')
                ->descriptionIcon(Phosphor::ClockDuotone)
                ->color('warning'),
        ];
    }
}
