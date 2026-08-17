<?php

namespace App\Filament\Widgets\Reports;

use App\Enums\DealStatus;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyRevenueChartWidget extends ChartWidget
{
    use HasDashboardScope;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '300px';

    public function getHeading(): ?string
    {
        $period = $this->getSelectedPeriod();
        $diffInDays = (int) $period['start']->diffInDays($period['end']);

        if ($diffInDays < 30) {
            if ($period['start']->format('Y-m') === $period['end']->format('Y-m')) {
                return 'Faturamento de ' . ucfirst($period['start']->translatedFormat('F \d\e Y'));
            }

            return 'Faturamento (' . $period['start']->format('d/m/Y') . ' até ' . $period['end']->format('d/m/Y') . ')';
        }

        return 'Faturamento por Mês (' . ucfirst($period['start']->translatedFormat('M/Y')) . ' até ' . ucfirst($period['end']->translatedFormat('M/Y')) . ')';
    }

    protected function getData(): array
    {
        $period = $this->getSelectedPeriod();
        $diffInDays = (int) $period['start']->diffInDays($period['end']);
        $isDaily = $diffInDays < 30;

        $deals = Deal::query()
            ->where('status', DealStatus::WON)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->tap(fn ($q) => $this->scopeByStatus($q))
            ->get(['created_at', 'total_value']);

        $chartData = [];

        if ($isDaily) {
            $current = $period['start']->copy()->startOfDay();
            $end = $period['end']->copy()->endOfDay();

            while ($current->lte($end)) {
                $key = $current->format('Y-m-d');
                $chartData[$key] = [
                    'label' => $current->format('d/m'),
                    'total' => 0,
                ];
                $current->addDay();
            }

            foreach ($deals as $deal) {
                $dayKey = Carbon::parse($deal->created_at)->format('Y-m-d');
                if (isset($chartData[$dayKey])) {
                    $chartData[$dayKey]['total'] += (float) $deal->total_value;
                }
            }
        } else {
            $currentMonth = $period['start']->copy()->startOfMonth();
            $endMonth = $period['end']->copy()->endOfMonth();

            while ($currentMonth->lte($endMonth)) {
                $key = $currentMonth->format('Y-m');
                $chartData[$key] = [
                    'label' => ucfirst($currentMonth->translatedFormat('M/Y')),
                    'total' => 0,
                ];
                $currentMonth->addMonth();
            }

            foreach ($deals as $deal) {
                $monthKey = Carbon::parse($deal->created_at)->format('Y-m');
                if (isset($chartData[$monthKey])) {
                    $chartData[$monthKey]['total'] += (float) $deal->total_value;
                }
            }
        }

        $labels = array_column($chartData, 'label');
        $totals = array_column($chartData, 'total');

        return [
            'datasets' => [
                [
                    'label' => $isDaily ? 'Faturamento Diário (R$)' : 'Faturamento Mensal (R$)',
                    'data' => $totals,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => '#3b82f6',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
