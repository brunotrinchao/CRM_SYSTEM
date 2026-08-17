<?php

namespace App\Filament\Widgets;

use App\Enums\DealStatus;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;

class SalesFunnelDealListOverview extends Widget
{
    use HasDashboardScope;
    use InteractsWithPageFilters;

    public ?array $tableFilters = null;
    public ?string $tableSearch = null;

    #[On('table-filters-updated')]
    public function onTableFiltersUpdated(?array $tableFilters = null, ?string $tableSearch = null): void
    {
        $this->tableFilters = $tableFilters;
        $this->tableSearch = $tableSearch;
    }

    protected string $view = 'filament.widgets.sales-funnel-overview';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        // Tenta obter filtros da página pai (ListDeals) em tempo real
        $page = null;
        if (method_exists($this, 'getLivewire')) {
            $page = $this->getLivewire();
        }
        if (! $page && method_exists($this, 'getPage')) {
            $page = $this->getPage();
        }

        $tableFilters = $this->tableFilters;
        $search = $this->tableSearch;

        if ($page && property_exists($page, 'tableFilters')) {
            $tableFilters = $page->tableFilters ?: $tableFilters;
        }
        if ($page && property_exists($page, 'tableSearch')) {
            $search = $page->tableSearch !== null ? $page->tableSearch : $search;
        }

        $tableFilters = $tableFilters ?? $this->pageFilters ?? [];

        // Extrai status (múltiplos ou único)
        $rawStatus = data_get($tableFilters, 'status.values') 
            ?? data_get($tableFilters, 'status.value') 
            ?? data_get($tableFilters, 'status');

        $statuses = [];
        if (is_array($rawStatus)) {
            foreach (Arr::flatten($rawStatus) as $val) {
                if (filled($val)) {
                    $statuses[] = (string) $val;
                }
            }
        } elseif (filled($rawStatus)) {
            $statuses[] = (string) $rawStatus;
        }

        // Extrai vendedor responsável
        $userId = data_get($tableFilters, 'user_id.value') ?? data_get($tableFilters, 'user_id');
        if (is_array($userId)) {
            $userId = Arr::first(Arr::flatten($userId));
        }

        // Extrai criador
        $createdBy = data_get($tableFilters, 'created_by.value') ?? data_get($tableFilters, 'created_by');
        if (is_array($createdBy)) {
            $createdBy = Arr::first(Arr::flatten($createdBy));
        }

        // Extrai desconto pendente
        $pendingDiscount = data_get($tableFilters, 'has_pending_discount.pending') ?? data_get($tableFilters, 'has_pending_discount');
        if (is_array($pendingDiscount)) {
            $pendingDiscount = $pendingDiscount['pending'] ?? false;
        }

        $query = Deal::query()
            ->tap(fn ($q) => $this->scopeByProfile($q))
            ->when(! empty($statuses), function ($q) use ($statuses) {
                $q->whereIn('status', $statuses);
            })
            ->when(filled($userId), function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->when(filled($createdBy), function ($q) use ($createdBy) {
                $q->where('created_by', $createdBy);
            })
            ->when($pendingDiscount, function ($q) {
                $q->whereHas('discountRequests', fn ($sub) => $sub->where('status', 'PENDING'));
            })
            ->when(filled($search), function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            });

        // Filtro por intervalo de datas se fornecido no filtro da tabela
        $dateRange = data_get($tableFilters, 'actual_close_date.actual_close_date') ?? data_get($tableFilters, 'actual_close_date');
        if (is_string($dateRange) && str_contains($dateRange, ' - ')) {
            [$startDate, $endDate] = explode(' - ', $dateRange, 2);
            try {
                $start = \Carbon\Carbon::createFromFormat('d/m/Y', trim($startDate))->startOfDay();
                $end = \Carbon\Carbon::createFromFormat('d/m/Y', trim($endDate))->endOfDay();
                $query->whereBetween('actual_close_date', [$start, $end]);
            } catch (\Throwable $e) {}
        }

        $stages = $query
            ->selectRaw('status, COUNT(*) as count, SUM(total_value) as total')
            ->groupBy('status')
            ->get()
            ->keyBy(function ($item) {
                return $item->status instanceof DealStatus ? $item->status->value : (string) $item->status;
            });

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
