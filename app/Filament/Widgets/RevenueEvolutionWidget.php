<?php

namespace App\Filament\Widgets;

use App\Enums\DealStatus;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal;
use Illuminate\Support\Number;
use LaBoiteACode\FilamentDashboardWidgets\Data\Trend;
use LaBoiteACode\FilamentDashboardWidgets\Data\TrendPoint;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\TrendWidget;

class RevenueEvolutionWidget extends TrendWidget
{
    use HasDashboardScope;
    // protected static ?string $heading = 'Evolução do Faturamento';

    protected int | string | array $columnSpan = [
        'lg' => 3,
    ];

    protected ?string $maxHeight = '300px';

    protected function getTrend(): Trend
    {
        // Período selecionado no dashboard (fallback: mês atual)
        $period = $this->getSelectedPeriod();
        $prev = $this->getPreviousPeriod($period);

        // Faturamento (deals ganhos) do período selecionado
        $total = (float) Deal::query()
            ->where('status', DealStatus::WON)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->sum('total_value');

        // Faturamento do período anterior (mesma duração) para a tendência
        $previousTotal = (float) Deal::query()
            ->where('status', DealStatus::WON)
            ->whereBetween('created_at', [$prev['start'], $prev['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->sum('total_value');

        // Faturamento agrupado por dia no período selecionado
        $totalsByDay = Deal::query()
            ->where('status', DealStatus::WON)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->selectRaw('DATE(created_at) as date, SUM(total_value) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // Preenche todos os dias do período (dias sem faturamento = 0)
        // para a linha do gráfico não "pular" datas vazias.
        $trendPoints = [];
        for ($day = $period['start']->copy(); $day->lte($period['end']); $day->addDay()) {
            $trendPoints[] = TrendPoint::make(
                $day->format('d/m'),
                (float) ($totalsByDay[$day->format('Y-m-d')] ?? 0),
            );
        }

        return Trend::make('Evolução do Faturamento')
            ->value($total)
            ->formatUsing(fn (float $value) => Number::currency($value, 'BRL'))
            ->comparison($this->calculateTrend($total, $previousTotal))
            ->points($trendPoints)
            ->type('area')
            ->color('primary');
    }
}
