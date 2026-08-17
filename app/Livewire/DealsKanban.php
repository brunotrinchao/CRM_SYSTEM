<?php

namespace App\Livewire;

use App\Enums\DealStatus;
use App\Models\Deal;
use App\Services\DealService;
use Filament\Notifications\Notification;
use Livewire\Component;

class DealsKanban extends Component
{
    public ?Deal $pendingCancelDeal = null;
    public ?Deal $pendingLostDeal = null;

    public bool $showCancelModal = false;
    public bool $showLostModal = false;

    public array $tableFilters = [];
    public string $search = '';

    // ID do componente Livewire pai (ListDeals), usado pelo card do Kanban para chamar
    // openDealView() diretamente via Livewire.find(parentId).call(...). Um dispatch de
    // evento de navegador (#[On]) não força um re-render completo do slideover nesse
    // fluxo entre componentes; a chamada direta sim.
    public string $parentId = '';

    protected $listeners = [
        'refresh-kanban' => '$refresh',
        'table-filters-updated' => 'handleTableFiltersUpdated',
    ];

    public function mount(array $tableFilters = [], string $tableSearch = '', string $parentId = ''): void
    {
        $this->tableFilters = $tableFilters;
        $this->search = $tableSearch;
        $this->parentId = $parentId;
    }

    public function handleTableFiltersUpdated($tableFilters = [], $tableSearch = ''): void
    {
        $this->tableFilters = (array) $tableFilters;
        $this->search = (string) $tableSearch;
    }

    public function openDealView(int $dealId): void
    {
        $this->dispatch('open-deal-view', id: $dealId);
    }

    public function moveDeal(int $dealId, string $targetStatusValue): void
    {
        $deal = Deal::find($dealId);
        if (! $deal) {
            return;
        }

        $targetStatus = DealStatus::tryFrom($targetStatusValue);
        if (! $targetStatus || $deal->status === $targetStatus) {
            return;
        }

        // 1. Negócios Cancelados não podem ser movidos
        if ($deal->status === DealStatus::CANCELLED) {
            Notification::make()
                ->title('Ação não permitida')
                ->body('Negócios cancelados não podem ter o status alterado.')
                ->danger()
                ->send();
            return;
        }

        // 2. Regras a partir de PENDENTE
        if ($deal->status === DealStatus::PENDING) {
            if ($targetStatus === DealStatus::NEGOTIATING) {
                DealService::update($deal, ['status' => DealStatus::NEGOTIATING->value]);
                Notification::make()
                    ->title('Status Atualizado')
                    ->body("O negócio '{$deal->title}' avançou para Negociação.")
                    ->success()
                    ->send();
                return;
            }

            if ($targetStatus === DealStatus::CANCELLED) {
                $this->pendingCancelDeal = $deal;
                $this->showCancelModal = true;
                return;
            }

            Notification::make()
                ->title('Transição Não Permitida')
                ->body('Negócios em Pendente só podem avançar para Negociação ou serem Cancelados.')
                ->warning()
                ->send();
            return;
        }

        // 3. Regras a partir de NEGOCIAÇÃO
        if ($deal->status === DealStatus::NEGOTIATING) {
            if ($targetStatus === DealStatus::WON) {
                DealService::update($deal, ['status' => DealStatus::WON->value]);
                Notification::make()
                    ->title('Negócio Ganho! 🎉')
                    ->body("O negócio '{$deal->title}' foi concluído como Ganho.")
                    ->success()
                    ->send();
                return;
            }

            if ($targetStatus === DealStatus::LOST) {
                $this->pendingLostDeal = $deal;
                $this->showLostModal = true;
                return;
            }

            if ($targetStatus === DealStatus::CANCELLED) {
                $this->pendingCancelDeal = $deal;
                $this->showCancelModal = true;
                return;
            }

            Notification::make()
                ->title('Transição Não Permitida')
                ->body('Negócios em Negociação não podem retornar para Pendente.')
                ->warning()
                ->send();
            return;
        }

        // 4. Negócios já concluídos (WON / LOST)
        if (in_array($deal->status, [DealStatus::WON, DealStatus::LOST])) {
            Notification::make()
                ->title('Negócio Finalizado')
                ->body('Este negócio já foi concluído e não pode ser movido.')
                ->warning()
                ->send();
            return;
        }
    }

    public function executeCancelDeal(): void
    {
        if (! $this->pendingCancelDeal) {
            return;
        }

        DealService::update($this->pendingCancelDeal, [
            'status' => DealStatus::CANCELLED->value,
            'confirm_status_cancelled' => true,
        ]);

        Notification::make()
            ->title('Negócio Cancelado')
            ->body("O negócio '{$this->pendingCancelDeal->title}' foi cancelado.")
            ->success()
            ->send();

        $this->closeCancelModal();
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->pendingCancelDeal = null;
    }

    public function executeLostDeal(): void
    {
        if (! $this->pendingLostDeal) {
            return;
        }

        DealService::update($this->pendingLostDeal, [
            'status' => DealStatus::LOST->value,
            'confirm_status_lost' => true,
        ]);

        Notification::make()
            ->title('Negócio Perdido')
            ->body("O negócio '{$this->pendingLostDeal->title}' foi marcado como Perdido.")
            ->warning()
            ->send();

        $this->closeLostModal();
    }

    public function closeLostModal(): void
    {
        $this->showLostModal = false;
        $this->pendingLostDeal = null;
    }

    public function render()
    {
        $query = Deal::query()
            ->with(['client', 'user', 'discountRequests', 'products']);

        // 1. Busca por texto (título, cliente, vendedor, produtos)
        if (filled($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('products', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        // 2. Filtro de Vendedor Responsável (user_id)
        if (!empty($this->tableFilters['user_id']['value'])) {
            $query->where('user_id', $this->tableFilters['user_id']['value']);
        }

        // 3. Filtro de Criado Por (created_by)
        if (!empty($this->tableFilters['created_by']['value'])) {
            $query->where('created_by', $this->tableFilters['created_by']['value']);
        }

        // 4. Filtro de Desconto Pendente (has_pending_discount)
        if (!empty($this->tableFilters['has_pending_discount']['pending'])) {
            $query->whereHas('discountRequests', function ($dq) {
                $dq->where('status', 'PENDING');
            });
        }

        // 5. Filtro de Status (status)
        if (!empty($this->tableFilters['status']['values']) && is_array($this->tableFilters['status']['values'])) {
            $query->whereIn('status', $this->tableFilters['status']['values']);
        }

        // 6. Filtro de Data de Ganho (actual_close_date)
        $dateRange = $this->tableFilters['actual_close_date']['actual_close_date'] ?? null;
        if (filled($dateRange)) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $query->whereBetween('actual_close_date', [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
            }
        }

        // 7. Registros Excluídos (trashed)
        $trashedMode = $this->tableFilters['trashed']['value'] ?? null;
        if ($trashedMode === 'with') {
            $query->withTrashed();
        } elseif ($trashedMode === 'only') {
            $query->onlyTrashed();
        }

        $allDeals = $query->get();

        $dealsByStatus = collect(DealStatus::cases())->mapWithKeys(function ($status) use ($allDeals) {
            return [$status->value => $allDeals->where('status', $status)];
        });

        return view('livewire.deals-kanban', [
            'deals' => $dealsByStatus,
        ]);
    }
}
