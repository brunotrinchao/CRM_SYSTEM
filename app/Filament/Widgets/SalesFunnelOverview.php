<?php

namespace App\Filament\Widgets;

use App\Enums\DealStatus;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal;
use Filament\Widgets\Widget;

class SalesFunnelOverview extends Widget
{
    use HasDashboardScope;

    protected string $view = 'filament.widgets.sales-funnel-overview';

    // Ocupa 3 colunas (3/4 da largura no grid de 4 colunas)
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'lg' => 3,
    ];

    // Método que retorna os dados para a View
    protected function getViewData(): array
    {
        $period = $this->getSelectedPeriod();

        // Base da query: escopo de perfil + período do dashboard
        $query = Deal::query()
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q));

        // Agrupa por status e calcula contagem e soma dos valores
        $stages = $query
            ->selectRaw('status, COUNT(*) as count, SUM(total_value) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $data = [];
        foreach (DealStatus::cases() as $status) {
            $row = $stages->get($status->value);

            $data[] = [
                'title' => $status->label(),
                'color' => $status->color(),
                'count' => $row->count ?? 0,
                'value' => (float) ($row->total ?? 0),
            ];
        }

        return ['stages' => $data];
    }
}
