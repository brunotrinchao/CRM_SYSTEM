<?php

namespace App\Filament\Resources\Deals\Tables;

use App\Components\Form\Textarea;
use App\Components\Form\Toggle;
use App\Enums\DealStatus;
use App\Enums\DiscountRequestStatus;
use App\Enums\UserProfile;
use App\Filament\Actions\SimpleActions;
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
use BokshornIt\FilamentActivityTimeline\Actions\ActivityTimelineAction;
use Filament\Actions\Action;
use Filament\Forms\Components\View;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\View as ComponentsView;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DealsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('products.name')
                    ->label('Produto')
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(1)
                    ->expandableLimitedList()
                    ->separator(','),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(DealStatus $state): string => $state->color())
                    ->formatStateUsing(fn(DealStatus $state): string => $state->label()),
                TextColumn::make('user.name')
                    ->label('Vendedor Responsável')
                    ->searchable()
                    ->sortable(),
                // TextColumn::make('creator.name')
                //     ->label('Criado Por')
                //     ->searchable()
                //     ->sortable(),
                TextColumn::make('total_value')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable()
                    ->extraAttributes(fn(): array => ['class' => 'font-finance']),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->date('d/m/Y')
                    ->sortable()
            ])
            ->recordClasses(function (Deal $record) {
                // Pega a primeira solicitação pendente ou ajusta conforme sua regra de negócio
                $latestDiscount = $record->discountRequests()->latest()->first();

                if (!$latestDiscount) {
                    return null;
                }

                // Aplica a borda esquerda grossa de acordo com o status
                return match ($latestDiscount->status->value) {
                    'PENDING' => 'border-l-4 border-l-warning-500',
                    'APPROVED' => 'border-l-4 border-l-success-500',
                    'REJECTED' => 'border-l-4 border-l-danger-500',
                    default => null,
                };
            })
            ->filters([
                // TrashedFilter::make(),
                SelectFilter::make('user_id')
                    ->label('Vendedor Responsável')
                    ->relationship('user', 'name', fn($query) => $query->where('profile', UserProfile::USER)),
                SelectFilter::make('created_by')
                    ->label('Criado Por')
                    ->relationship('creator', 'name', fn($query) => $query->whereIn('profile', [UserProfile::ADMIN, UserProfile::MANAGER])),
                Filter::make('has_pending_discount')
                    ->label('Com Desconto Pendente')
                    ->indicator('Desconto Pendente')
                    ->schema([
                        Toggle::make('pending')
                            ->label('Apenas com desconto pendente')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['pending'] ?? false,
                            fn(Builder $query) => $query->whereHas('discountRequests', function ($q) {
                                $q->where('status', 'PENDING');
                            })
                        );
                    }),
                SelectFilter::make('status')
                    ->multiple()
                    ->options(DealStatus::options()),
                Filter::make('actual_close_date')
                    ->label('Data de Ganho')
                    ->indicator(function (array $data): ?string {
                        $range = $data['actual_close_date'] ?? null;

                        if (blank($range)) {
                            return null;
                        }

                        $dates = explode(' - ', $range);

                        if (count($dates) !== 2) {
                            return null;
                        }

                        $from = \Illuminate\Support\Carbon::parse($dates[0])->format('d/m/Y');
                        $until = \Illuminate\Support\Carbon::parse($dates[1])->format('d/m/Y');

                        return "Data de Ganho: {$from} até {$until}";
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $range = $data['actual_close_date'] ?? null;

                        if (blank($range)) {
                            return $query;
                        }

                        $dates = explode(' - ', $range);

                        if (count($dates) !== 2) {
                            return $query;
                        }

                        return $query->whereBetween('actual_close_date', [
                            $dates[0] . ' 00:00:00',
                            $dates[1] . ' 23:59:59',
                        ]);
                    }),
            ])
            ->filtersLayout(FiltersLayout::Modal)
            // Botão nativo de filtros escondido: os filtros agora são controlados por um
            // painel único em list-deals.blade.php (ListDeals::getFiltersAction()), que
            // afeta Tabela e Kanban ao mesmo tempo. Os Filter objects abaixo continuam
            // registrados normalmente — só o gatilho/UI nativo do Filament é que some.
            ->filtersTriggerAction(fn ($action) => $action->hidden())
            ->recordUrl(null)
            ->recordAction('custom_view')
            ->recordActions([
                SimpleActions::getViewWithEditAndDelete(
                    width: Width::Large,
                    schemaCallback: fn($schema) => DealForm::configure($schema),
                    schemaViewCallback: fn(Schema $schema) => DealInfolist::configure($schema),
                    actionCallback: fn(Model $record, array $data) => DealService::update($record, $data),
                    model: Deal::class,
                    recordName: 'Negócio',
                    recordAction: fn(Deal $record): bool => $record->status !== DealStatus::CANCELLED,
                    deleteAction: fn(Deal $record): bool => $record->canBeDeleted(),
                    modal: false,
                    relations: ['client', 'products', 'discountRequests'],
                    extraFooterActions: [
                        fn(Deal $record) => Action::make('whatsapp_message')
                            ->label('Enviar WhatsApp')
                            ->icon(Phosphor::WhatsappLogo)
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
                        // Closure para capturar o $record (Deal) do modal pai e injetar total_value
                        fn(Deal $record) => SimpleActions::getCreateModal(
                            width: Width::Large,
                            schemaCallback: fn($schema) => DealDiscountForm::configure($schema),
                            actionCallback: fn(array $data) => DealService::requestDicount($data),
                            recordName: 'Solicitar Desconto',
                            buttonColor: 'primary', // Evite deixar vazio se o componente exigir string
                            model: DiscountRequest::class,
                            modal: false,
                            name: 'request_discount',
                            labelButton: 'Solicitar', // Ajustado para o padrão comum (verifique se na sua helper é labelButon ou label)
                            iconButton: Phosphor::SealPercent,
                            defaults: fn() => ['deal_id' => $record->id],
                            disabled: ($record->hasPendingDiscount() && Auth::user()?->profile === UserProfile::USER->value),
                        )
                        ->visible(in_array($record->status, [DealStatus::PENDING, DealStatus::NEGOTIATING]))
                        ->fillForm(fn() => [
                                'total_value' => $record->total_value,
                            ]),
                        fn(Deal $record) => SimpleActions::getCreateModal(
                            width: Width::Large,
                            schemaCallback: fn($schema) => NotesForm::configure($schema, true),
                            actionCallback: fn(array $data) => DealNoteService::create($data),
                            recordName: 'Contato',
                            model: DealNote::class,
                            modal: false,
                            name: 'create_deal_note_modal',
                            iconButton: Phosphor::PhonePlus,
                            defaults: fn() => ['deal_id' => $record->id],
                        )
                            ->size(Size::Small)
                            ->visible(in_array($record->status, [DealStatus::PENDING, DealStatus::NEGOTIATING])),
                        fn(Deal $record) => Action::make('transfer_deal')
                            ->label("Tranferir negócio")
                            ->modalIcon(Phosphor::ArrowsLeftRightThin)
                            ->modalIconColor('primary')
                            ->modalSubmitActionLabel('Transferir')
                            ->modalCancelAction(false)
                            ->color(Color::Neutral)
                            ->icon(Phosphor::ArrowsLeftRight)
                            ->size(Size::ExtraLarge)
                            ->schema(fn($schema) => DealTransfer::configure($schema))
                            ->hidden(Auth::user()->profile === UserProfile::USER)
                            ->fillForm(fn(Deal $record): array => [
                                'current_user_id' => $record->user_id, // Pré-preenche com o dono atual
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
                        fn(Deal $record) => ActivityTimelineAction::make()
                            ->withRelations(['client', 'products', 'user', 'discountRequests', 'notesList'])
                            ->icon(Phosphor::ClockCounterClockwise)
                            ->size(Size::ExtraLarge)
                            ->label('Histórico do negócio'),
                    ]
                )
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ]);
    }
}
