<?php

namespace App\Livewire;

use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Filament\Resources\Deals\Pages\ListDeals;
use App\Models\Deal;
use App\Services\DealService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DealsKanban extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public array $tableFilters = [];
    public string $search = '';

    protected $listeners = [
        'refresh-kanban' => '$refresh',
        'table-filters-updated' => 'handleTableFiltersUpdated',
    ];

    public function mount(array $tableFilters = [], string $tableSearch = ''): void
    {
        $this->tableFilters = $tableFilters;
        $this->search = $tableSearch;
    }

    public function handleTableFiltersUpdated($tableFilters = [], $tableSearch = ''): void
    {
        $this->tableFilters = (array) $tableFilters;
        $this->search = (string) $tableSearch;
    }

    // Espelha pra Tabela (ListDeals) qualquer busca digitada aqui no Kanban, pra que
    // filtro/busca afetem as duas visões independente de onde foi alterado.
    public function updatedSearch(): void
    {
        $this->dispatch('kanban-search-updated', search: $this->search);
    }

    // A action 'custom_view' (mesma usada pela Tabela e reaproveitada de
    // ListDeals::getCustomViewAction()) precisa ser cacheada localmente aqui — não dá
    // pra montá-la via cross-component (Livewire.find()/#[On] no ListDeals): o
    // slideover não renderiza visualmente quando o clique se origina de dentro deste
    // componente filho. Rodando aqui (mesmo componente do clique = mesmo componente
    // que renderiza <x-filament-actions::modals /> no blade), funciona.
    public function boot(): void
    {
        $this->cacheAction(ListDeals::getCustomViewAction());
        $this->cacheAction($this->getChangeDealStatusAction());
    }

    // Action nativa do Filament (modal de confirmação padrão do painel, em vez de HTML
    // customizado) usada por moveDeal() sempre que a transição de status exige
    // confirmação. Argumentos (dealId/targetStatus) chegam via mountAction().
    protected function getChangeDealStatusAction(): Action
    {
        return Action::make('change_deal_status')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('Confirmar')
            ->color(fn (array $arguments): string => match (DealStatus::from($arguments['targetStatus'])) {
                DealStatus::CANCELLED => 'danger',
                DealStatus::LOST => 'warning',
                DealStatus::WON => 'success',
                default => 'gray',
            })
            ->modalIcon(fn (array $arguments): string => match (DealStatus::from($arguments['targetStatus'])) {
                DealStatus::CANCELLED => 'heroicon-o-exclamation-triangle',
                DealStatus::LOST => 'heroicon-o-hand-thumb-down',
                DealStatus::WON => 'heroicon-o-trophy',
                default => 'heroicon-o-arrows-right-left',
            })
            ->modalHeading(fn (array $arguments): string => match (DealStatus::from($arguments['targetStatus'])) {
                DealStatus::CANCELLED => 'Confirmar Cancelamento',
                DealStatus::LOST => 'Confirmar Status Perdido',
                DealStatus::WON => 'Confirmar Negócio Ganho',
                default => 'Confirmar Alteração de Status',
            })
            ->modalDescription(function (array $arguments): string {
                $deal = Deal::find($arguments['dealId']);
                $targetStatus = DealStatus::from($arguments['targetStatus']);

                $description = "Tem certeza de que deseja alterar o status do negócio \"{$deal?->title}\" para {$targetStatus->label()}?";

                if ($targetStatus === DealStatus::CANCELLED) {
                    $description .= ' O cancelamento não poderá ser desfeito e o botão de edição será bloqueado.';
                }

                return $description;
            })
            ->action(function (array $arguments): void {
                $deal = Deal::find($arguments['dealId']);
                if (! $deal) {
                    return;
                }

                $targetStatus = DealStatus::from($arguments['targetStatus']);

                $data = ['status' => $targetStatus->value];
                if ($targetStatus === DealStatus::CANCELLED) {
                    $data['confirm_status_cancelled'] = true;
                }
                if ($targetStatus === DealStatus::LOST) {
                    $data['confirm_status_lost'] = true;
                }

                $dealTitle = $deal->title;
                DealService::update($deal, $data);

                Notification::make()
                    ->title('Status Atualizado')
                    ->body("O negócio '{$dealTitle}' foi movido para {$targetStatus->label()}.")
                    ->success()
                    ->send();
            });
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

        // 1. Negócios já Finalizados (Ganho, Perdido, Cancelado): perfil USER nunca
        // move; outros perfis podem, mas sempre com confirmação.
        if (in_array($deal->status, [DealStatus::WON, DealStatus::LOST, DealStatus::CANCELLED])) {
            if (Auth::user()?->profile === UserProfile::USER) {
                Notification::make()
                    ->title('Negócio Finalizado')
                    ->body('Este negócio já foi concluído e não pode ser movido.')
                    ->warning()
                    ->send();
                return;
            }

            $this->mountAction('change_deal_status', ['dealId' => $deal->id, 'targetStatus' => $targetStatus->value]);
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
                $this->mountAction('change_deal_status', ['dealId' => $deal->id, 'targetStatus' => $targetStatus->value]);
                return;
            }

            Notification::make()
                ->title('Transição Não Permitida')
                ->body('Negócios em Pendente só podem avançar para Negociação ou serem Cancelados.')
                ->warning()
                ->send();
            return;
        }

        // 3. Regras a partir de NEGOCIAÇÃO: Ganho, Perdido e Cancelado sempre com confirmação
        if ($deal->status === DealStatus::NEGOTIATING) {
            if (in_array($targetStatus, [DealStatus::WON, DealStatus::LOST, DealStatus::CANCELLED])) {
                $this->mountAction('change_deal_status', ['dealId' => $deal->id, 'targetStatus' => $targetStatus->value]);
                return;
            }

            Notification::make()
                ->title('Transição Não Permitida')
                ->body('Negócios em Negociação não podem retornar para Pendente.')
                ->warning()
                ->send();
            return;
        }
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
