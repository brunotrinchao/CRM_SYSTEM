<?php

namespace App\Filament\Widgets\Reports;

use App\Enums\DealStatus;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal;
use Filament\Widgets\Widget;
use Illuminate\Support\Number;

class DealsByStatusWidget extends Widget
{
    use HasDashboardScope;

    protected string $view = 'filament.widgets.reports.deals-by-status-widget';
    protected ?string $pollingInterval = null;

    public function getStatusSummary(): array
    {
        $period = $this->getSelectedPeriod();

        $deals = Deal::query()
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->tap(fn ($q) => $this->scopeByStatus($q))
            ->get(['status', 'total_value']);

        $summary = [];
        $totalCount = $deals->count();

        foreach (DealStatus::cases() as $statusEnum) {
            $matchingDeals = $deals->filter(fn ($d) => $d->status === $statusEnum);
            $count = $matchingDeals->count();
            $val = $matchingDeals->sum('total_value');
            $percentage = $totalCount > 0 ? ($count / $totalCount) * 100 : 0;

            $summary[] = [
                'status' => $statusEnum,
                'label' => $statusEnum->label(),
                'color' => $statusEnum->color(),
                'icon' => $statusEnum->icon(),
                'count' => $count,
                'total_value' => $val,
                'percentage' => round($percentage, 1),
            ];
        }

        return $summary;
    }
}
