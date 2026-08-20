<?php

namespace App\Filament\Resources\Deals\Pages;

use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Filament\Actions\ActivityTimelineAction;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Deals\DealResource;
use App\Filament\Resources\Deals\Schemas\DealDiscountForm;
use App\Filament\Resources\Deals\Schemas\DealForm;
use App\Filament\Resources\Deals\Schemas\DealInfolist;
use App\Filament\Resources\Deals\Schemas\DealTransfer;
use App\Filament\Resources\Notes\Schemas\NotesForm;
use App\Models\Deal;
use App\Models\DealNote;
use App\Models\DiscountRequest;
use App\Models\User;
use App\Services\DealNoteService;
use App\Services\DealService;

use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\View as ComponentsView;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListDeals extends ListRecords
{
    protected static string $resource = DealResource::class;

    protected string $view = 'filament.resources.deals.pages.list-deals';

    public string $activeView = 'listagem';

    public array $filters = [];

    // Painel de filtros único (list-deals.blade.php) que afeta Tabela e Kanban ao
    // mesmo tempo — substitui o botão nativo de filtros da Tabela (escondido em
    // DealsTable::configure() via ->filtersTriggerAction hidden). Os selects/toggle/
    // checkboxes do painel escrevem direto em $tableFilters (mesmo shape que os
    // Filter objects da tabela usam), então updatedTableFilters() já cuida de tudo.
    // A data (widget de terceiros só existe dentro do componente de Tabela) usa estes
    // dois campos simples em vez do date-range picker original.
    public ?string $filterDateFrom = null;
    public ?string $filterDateUntil = null;

    // Ouve o mesmo evento que o DealsKanban dispara/escuta pra manter Tabela e Kanban
    // sincronizados: quando uma nota ou solicitação de desconto promove um negócio de
    // Pendente pra Negociação a partir de QUALQUER um dos dois componentes (ver
    // promoteFromPendingToNegotiating()), o outro precisa re-renderizar também, já
    // que são componentes Livewire independentes.
    #[\Livewire\Attributes\On('refresh-kanban')]
    public function refreshFromOtherView(): void
    {
        // Vazio de propósito: só precisa disparar um novo ciclo de render() — a
        // query dos negócios já é refeita do zero a cada render.
    }

    // Espelha na Tabela a busca digitada no Kanban (DealsKanban::updatedSearch()),
    // pra que o filtro/busca de qualquer uma das duas visões afete as duas.
    #[\Livewire\Attributes\On('kanban-search-updated')]
    public function handleKanbanSearchUpdated(string $search): void
    {
        $this->tableSearch = $search;
        $this->syncWidgetFilters();
    }

    // Chamado via `afterCreate` das actions de Contato e Solicitar Desconto (Tabela e
    // Kanban reaproveitam a mesma getCustomViewAction()). Regra de negócio: criar uma
    // nota ou solicitar desconto com sucesso em negócio Pendente avança ele pra
    // Negociação automaticamente.
    private static function promoteFromPendingToNegotiating(Deal $deal, $livewire): void
    {
        if ($deal->status !== DealStatus::PENDING) {
            return;
        }

        DealService::update($deal, ['status' => DealStatus::NEGOTIATING->value]);

        Notification::make()
            ->title('Status Atualizado')
            ->body("O negócio '{$deal->title}' avançou para Negociação.")
            ->success()
            ->send();

        if (method_exists($livewire, 'dispatch')) {
            $livewire->dispatch('refresh-kanban');
        }
    }

    #[\Livewire\Attributes\On('assign-seller')]
    public function assignSeller(int $sellerId): void
    {
        if (isset($this->mountedTableActionsData[0])) {
            $this->mountedTableActionsData[0]['user_id'] = $sellerId;
        }

        if (property_exists($this, 'data') && is_array($this->data)) {
            $this->data['user_id'] = $sellerId;
        }

        try {
            $this->unmountAction(cancelParentActions: false);
        } catch (\Throwable $e) {}
    }

    public function mount(): void
    {
        parent::mount();
        $this->syncWidgetFilters();
    }

    public function updatedTableFilters(): void
    {
        parent::updatedTableFilters();
        $this->sanitizeStatusFilterValues();
        $this->syncWidgetFilters();
    }

    // Rede de segurança: garante que tableFilters.status.values seja sempre um array
    // plano de strings. O SelectFilter nativo do Filament faz array_flip() nesse valor
    // pra montar o indicador "Filtros ativos" — qualquer entrada não-escalar aí quebra
    // com "array_flip(): Can only flip string and integer values".
    private function sanitizeStatusFilterValues(): void
    {
        $values = $this->tableFilters['status']['values'] ?? null;

        if (! is_array($values)) {
            return;
        }

        $this->tableFilters['status']['values'] = collect($values)
            ->flatten()
            ->filter(fn ($value) => is_string($value) || is_int($value))
            ->values()
            ->all();
    }

    public function applyTableFilters(): void
    {
        parent::applyTableFilters();
        $this->syncWidgetFilters();
    }

    public function updatedTableSearch(): void
    {
        parent::updatedTableSearch();
        $this->syncWidgetFilters();
    }

    private function syncWidgetFilters(): void
    {
        $this->dispatch('table-filters-updated', tableFilters: $this->tableFilters, tableSearch: $this->tableSearch);
    }

    // Campos simples de data do painel único de filtros (ver comentário em
    // $filterDateFrom). Escrevem no mesmo formato "Y-m-d - Y-m-d" que
    // render()/DealsKanban::render() já esperam em tableFilters.actual_close_date.
    public function updatedFilterDateFrom(): void
    {
        $this->applyDateRangeFilter();
    }

    public function updatedFilterDateUntil(): void
    {
        $this->applyDateRangeFilter();
    }

    private function applyDateRangeFilter(): void
    {
        $isActive = (bool) ($this->filterDateFrom && $this->filterDateUntil);

        // Filter::make('actual_close_date') não tem ->schema() próprio, então o
        // Filament usa o reset state padrão dele: ['isActive' => false]. Sem
        // marcar isActive aqui, InteractsWithTableQuery::apply() sempre pula o
        // filtro (checa $data['isActive']), mesmo com o range preenchido.
        $this->tableFilters['actual_close_date']['isActive'] = $isActive;
        $this->tableFilters['actual_close_date']['actual_close_date'] = $isActive
            ? "{$this->filterDateFrom} - {$this->filterDateUntil}"
            : null;

        $this->syncWidgetFilters();
    }

    public function clearAllFilters(): void
    {
        $this->tableFilters = [];
        $this->filterDateFrom = null;
        $this->filterDateUntil = null;
        $this->syncWidgetFilters();
    }

    protected function getActions(): array
    {
        return [static::getCustomViewAction()];
    }

    // Extraído para método estático público para que DealsKanban (componente Livewire
    // separado usado na visão Kanban) também possa cachear e montar a mesma action
    // 'custom_view' localmente — ver DealsKanban::boot(). Chamar mountAction() nela a
    // partir de outro componente via evento/Livewire.find() não faz o slideover
    // renderizar visualmente (bug observado: estado monta no servidor, mas o modal
    // não abre no cliente quando o clique se origina de dentro do componente Kanban).
    public static function getCustomViewAction(): \Filament\Actions\ViewAction
    {
        return SimpleActions::getViewWithEditAndDelete(
            width: Width::Large,
            schemaCallback: fn ($schema) => DealForm::configure($schema),
            schemaViewCallback: fn (Schema $schema) => DealInfolist::configure($schema),
            actionCallback: fn (Model $record, array $data) => DealService::update($record, $data),
            model: Deal::class,
            recordName: 'Negócio',
            recordAction: fn (Deal $record): bool => $record->status !== DealStatus::CANCELLED,
            deleteAction: fn (Deal $record): bool => $record->canBeDeleted(),
            modal: false,
            relations: ['client', 'products', 'discountRequests'],
            extraFooterActions: [
                fn (Deal $record) => Action::make('whatsapp_message')
                    ->label('Enviar WhatsApp')
                    ->icon(Phosphor::WhatsappLogoThin)
                    ->color(Color::Emerald)
                    ->size(Size::ExtraLarge)
                    ->visible(in_array($record->status, [DealStatus::NEGOTIATING]))
                    ->modalHeading('Mensagem para WhatsApp')
                    ->modalIcon(Phosphor::WhatsappLogoThin)
                    ->modalWidth(Width::SevenExtraLarge)
                    ->modalSubmitActionLabel('Abrir WhatsApp Web')
                    ->modalCancelActionLabel('Cancelar')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                Group::make([
                                    Textarea::make('message', 'Mensagem da Proposta', [
                                        'rows' => 14,
                                        'speechDictation' => false,
                                        'emojiPicker' => false,
                                        'characterCounter' => false,
                                    ]),
                                ])->columnSpan(1),
                                Group::make([
                                    ComponentsView::make('filament.forms.components.whatsapp-modal'),
                                ])->columnSpan(1),
                            ]),
                    ])
                    ->fillForm(function (Deal $record): array {
                        $record->loadMissing(['client', 'user', 'products.photos']);
                        $record->loadMissing(['products.photos', 'user', 'client', 'discountRequests']);

                        $sellerName = $record->user?->name ?? Auth::user()?->name ?? 'Consultor Comercial';
                        $clientName = $record->client?->name ?? 'Cliente';
                        $dealTitle = $record->title;
                        $enterpriseName = env('ENTERPRISE_NAME') ?: config('app.name', 'nossa empresa');

                        $lines = [];
                        $lines[] = "Olá, *{$clientName}*! 👋";
                        $lines[] = "";
                        $lines[] = "Aqui é o *{$sellerName}*, seu consultor da *{$enterpriseName}*. Passando para apresentar os detalhes da proposta do seu negócio: *{$dealTitle}*.";
                        $lines[] = "";
                        $lines[] = "📦 *PRODUTOS / SERVIÇOS:*";

                        $subtotal = 0;
                        $pivotDiscount = 0;
                        $productThumbnails = [];

                        if ($record->products->isNotEmpty()) {
                            foreach ($record->products as $product) {
                                $qty = $product->pivot->quantity ?? 1;
                                $unitPrice = (float) ($product->pivot->unit_price ?? $product->price ?? 0);
                                $itemSubtotal = $unitPrice * $qty;
                                $itemDiscount = (float) ($product->pivot->discount ?? 0);

                                $subtotal += $itemSubtotal;
                                $pivotDiscount += $itemDiscount;

                                $unitPriceFormatted = number_format($unitPrice, 2, ',', '.');
                                $itemSubtotalFormatted = number_format($itemSubtotal, 2, ',', '.');

                                $lines[] = "• *{$product->name}*";
                                $lines[] = "  Qtde: {$qty} | Valor Unit.: R$ {$unitPriceFormatted} | Subtotal: R$ {$itemSubtotalFormatted}";

                                if ($product->photos && $product->photos->isNotEmpty()) {
                                    foreach ($product->photos as $photo) {
                                        if (filled($photo->image)) {
                                            $imageUrl = \App\Models\ProductPhoto::cleanImageUrl($photo->image);

                                            $productThumbnails[] = [
                                                'product_name' => $product->name,
                                                'image_url' => $imageUrl,
                                            ];
                                        }
                                    }
                                }
                            }
                        } else {
                            $subtotal = (float) $record->total_value;
                            $lines[] = "• *{$dealTitle}*";
                            $lines[] = "  Valor: R$ " . number_format($subtotal, 2, ',', '.');
                        }

                        $subtotal = round($subtotal, 2);
                        $totalValue = round((float) $record->total_value, 2);
                        $dealDiscount = round((float) ($record->discount ?? 0), 2);

                        $approvedReq = $record->discountRequests
                            ? $record->discountRequests->where('status', \App\Enums\DiscountRequestStatus::APPROVED)->first()
                            : null;

                        if ($approvedReq) {
                            if ($approvedReq->type === \App\Enums\DiscountRequestType::PERCENT) {
                                $dealDiscount = max($dealDiscount, round($subtotal * ((float) $approvedReq->amount / 100), 2));
                            } else {
                                $dealDiscount = max($dealDiscount, round((float) $approvedReq->amount, 2));
                            }
                        }

                        $diffDiscount = ($subtotal - $totalValue) >= 0.01 ? round($subtotal - $totalValue, 2) : 0;
                        $totalDiscount = max($pivotDiscount, $dealDiscount, $diffDiscount);

                        if ($totalDiscount >= 0.01 && abs(($subtotal - $totalDiscount) - $totalValue) > 0.01 && abs($subtotal - $totalValue) < 0.01) {
                            $totalValue = max(0, round($subtotal - $totalDiscount, 2));
                        }

                        $subtotalFormatted = number_format($subtotal, 2, ',', '.');
                        $discountFormatted = number_format($totalDiscount, 2, ',', '.');
                        $totalFormatted = number_format($totalValue, 2, ',', '.');

                        $lines[] = "";
                        $lines[] = "💰 *RESUMO:*";
                        $lines[] = "• Subtotal: R$ {$subtotalFormatted}";
                        if ($totalDiscount >= 0.01) {
                            $lines[] = "• Desconto: R$ {$discountFormatted}";
                        }
                        $lines[] = "• *Valor Total: R$ {$totalFormatted}*";
                        $lines[] = "";
                        $lines[] = "Estou à disposição para tirarmos qualquer dúvida e avançarmos! 🚀";

                        return [
                            'message' => implode("\n", $lines),
                            'productThumbnails' => $productThumbnails,
                        ];
                    })
                    ->action(function (array $data, Deal $record, $livewire): void {
                        $text = rawurlencode($data['message'] ?? '');
                        $phone = preg_replace('/[^0-9]/', '', $record->client?->phone ?? $record->client?->mobile ?? '');

                        $url = filled($phone)
                            ? "https://api.whatsapp.com/send?phone={$phone}&text={$text}"
                            : "https://web.whatsapp.com/send?text={$text}";

                        if (method_exists($livewire, 'js')) {
                            $livewire->js("window.open('{$url}', '_blank')");
                        }
                    }),
                fn (Deal $record) => SimpleActions::getCreateModal(
                    width: Width::Large,
                    schemaCallback: fn ($schema) => DealDiscountForm::configure($schema),
                    actionCallback: fn (array $data) => DealService::requestDicount($data),
                    recordName: 'Solicitar Desconto',
                    buttonColor: 'primary',
                    model: DiscountRequest::class,
                    modal: false,
                    name: 'request_discount',
                    labelButton: 'Solicitar',
                    iconButton: Phosphor::SealPercentFill,
                    defaults: fn () => ['deal_id' => $record->id],
                    disabled: ($record->hasPendingDiscount() && Auth::user()?->profile === UserProfile::USER->value),
                    afterCreate: fn ($createdRecord, $livewire) => static::promoteFromPendingToNegotiating($record, $livewire),
                )
                ->visible(in_array($record->status, [DealStatus::PENDING, DealStatus::NEGOTIATING]))
                ->fillForm(fn () => [
                    'total_value' => $record->total_value,
                ]),
                fn (Deal $record) => SimpleActions::getCreateModal(
                    width: Width::Large,
                    schemaCallback: fn ($schema) => NotesForm::configure($schema, isDealForm: true),
                    actionCallback: fn (array $data) => DealNoteService::create($data),
                    recordName: 'Contato',
                    model: DealNote::class,
                    modal: false,
                    name: 'create_deal_note_modal',
                    iconButton: Phosphor::Phone,
                    defaults: fn () => ['deal_id' => $record->id],
                    afterCreate: fn ($createdRecord, $livewire) => static::promoteFromPendingToNegotiating($record, $livewire),
                )
                    ->size(Size::Small)
                    ->visible(in_array($record->status, [DealStatus::PENDING, DealStatus::NEGOTIATING])),
                fn (Deal $record) => Action::make('transfer_deal')
                    ->label('Tranferir negócio')
                    ->modalIcon(Phosphor::ArrowsLeftRightThin)
                    ->modalIconColor('primary')
                    ->modalSubmitActionLabel('Transferir')
                    ->modalCancelAction(false)
                    ->color(Color::Neutral)
                    ->icon(Phosphor::ArrowsLeftRightThin)
                    ->size(Size::ExtraLarge)
                    ->schema(fn ($schema) => DealTransfer::configure($schema))
                    ->hidden(Auth::user()->profile === UserProfile::USER)
                    ->fillForm(fn (Deal $record): array => [
                        'current_user_id' => $record->user_id,
                    ])
                    ->action(function (Deal $record, array $data): void {
                        $user = User::find($data['user_id']);
                        DealService::transfer($record, $user);

                        $bodyNotification = "O negócio {$record->title} foi transferido para {$user->name}.";
                        $user->notifications()->create([
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'type' => \Filament\Notifications\DatabaseNotification::class,
                            'data' => [
                                'title' => 'Negócio transferido com sucesso!',
                                'body' => $bodyNotification,
                                'icon' => Phosphor::CheckCircle,
                                'iconColor' => 'success',
                                'status' => 'success',
                                'format' => 'filament',
                            ],
                        ]);
                    }),
                fn (Deal $record) => ActivityTimelineAction::make()
                    ->withRelations(['client', 'products', 'user', 'discountRequests', 'notesList'])
                    ->icon(Phosphor::ClockCounterClockwiseThin)
                    ->size(Size::ExtraLarge)
                    ->label('Histórico do negócio'),
            ]
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deal_rules_help')
                ->label('Regras dos Negócios')
                ->icon(Phosphor::Question)
                ->color('info')
                ->modalHeading('Guia & Regras dos Negócios')
                ->modalIcon(Phosphor::BookOpenTextDuotone)
                ->modalWidth(Width::FiveExtraLarge)
                ->slideOver()
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Entendi')
                ->modalContent(view('filament.modals.deal-rules-help')),
            SimpleActions::getWizardCreateModal(
                width: Width::ExtraLarge,
                steps: DealForm::getSteps(),
                actionCallback: fn (array $data) => DealService::create($data),
                recordName: 'Negócio',
                model: Deal::class,
                modal: false,
                name: 'create_deal_modal',
            ),
        ];
    }

    protected function getHeaderWidgetsData(): array
    {
        return [
            'tableFilters' => $this->tableFilters,
            'tableSearch' => $this->tableSearch,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // SalesFunnelDealListOverview::class,
        ];
    }
}
