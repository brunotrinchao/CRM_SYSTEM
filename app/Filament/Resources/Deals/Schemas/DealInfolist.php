<?php

namespace App\Filament\Resources\Deals\Schemas;

use App\Enums\DealStatus;
use App\Components\Infolist\Date;
use App\Components\Infolist\Money;
use App\Components\Infolist\Text;
use App\Components\Infolist\Repeater;
use App\Enums\DiscountRequestStatus;
use App\Enums\DiscountRequestType;
use App\Enums\UserProfile;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Clients\Schemas\ClientInfolist;
use App\Filament\Resources\Notes\Schemas\NotesForm;
use App\Filament\Resources\Products\Schemas\ProductInfolist;
use App\Models\Client;
use App\Models\Deal;
use App\Models\DealNote;
use App\Models\DiscountRequest;
use App\Models\Product;
use App\Models\User;
use App\Services\DealNoteService;
use App\Services\DealService;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\ItemCard;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\ItemCardGroup;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\ItemCardStack;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\SegmentTabs;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\SegmentTabs\SegmentTab;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Callout;
use Filament\Support\Enums\TextSize;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DealInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SegmentTabs::make('')
                    ->fullWidth(true)
                    ->tabs([
                        SegmentTab::make('Dados')
                            ->schema([
                                ItemCardStack::make()
                                    ->schema([
                                        ItemCard::make(fn(Deal $record) => new HtmlString(
                                            'Informações do negócio <span class="fi-badge fi-size-xs fi-color dark:fi-text-color-200 fi-color-' . $record->status->color() . ' fi-text-color-700">' . $record->status->label() . '</span>'
                                        ))
                                            ->icon(Phosphor::InfoFill)
                                            ->extraAttributes(['class' => 'item-card--form-panel'])
                                            ->schema([
                                                Text::make('title')
                                                    ->label('Título')
                                                    ->columnSpanFull(),
                                                TextEntry::make('user.name')
                                                    ->label('Vendedor Responsável'),
                                                TextEntry::make('creator.name')
                                                    ->label('Criado Por'),
                                                TextEntry::make('client.name')
                                                    ->label('Cliente')
                                                    ->suffixAction(
                                                        SimpleActions::getViewInfolist(
                                                            width: Width::Large,
                                                            schemaViewCallback: fn(Schema $schema) => ClientInfolist::configure($schema),
                                                            model: Client::class,
                                                            recordName: 'Cliente',
                                                            modal: false,
                                                            relations: ['addresses', 'deals.products', 'deals.user', 'deals.notesList']
                                                        )
                                                    ),
                                            ]),
                                        ItemCardGroup::make('Produtos do Negócio')
                                            ->schema([
                                                RepeatableEntry::make('products')
                                                    ->extraAttributes(['class' => 'custom-clean-repeatable'])
                                                    ->hiddenLabel()
                                                    ->schema([
                                                        ItemCard::make(fn($record) => $record?->name ?? '')
                                                            ->pressable()
                                                            ->extraAttributes(['class' => 'item-card--form-panel'])
                                                            ->description(function ($record) {
                                                                if (!$record)
                                                                    return '';

                                                                $name = $record->name ?? '';
                                                                $quantity = $record->pivot?->quantity ?? $record->quantity ?? 1;

                                                                $priceValue = $record->price ?? 0;
                                                                $priceFormatted = 'R$ ' . number_format($priceValue, 2, ',', '.');
                                                                $totalFormatted = 'R$ ' . number_format($priceValue * $quantity, 2, ',', '.');

                                                                return "Unid.: {$quantity} | Preço: {$priceFormatted} | Total: {$totalFormatted}";
                                                            })
                                                            ->image(fn($record) => $record?->photos?->first()?->image)
                                                            ->action(
                                                                Action::make('custom_view')
                                                                    ->label("Visualizar")
                                                                    ->modalHeading("Visualizar")
                                                                    ->modalWidth(Width::Large)
                                                                    ->modalIcon(fn($record) => $record?->getIcon())
                                                                    ->icon('heroicon-o-information-circle')
                                                                    ->color(Color::Neutral)
                                                                    ->schema(fn(Schema $schema) => ProductInfolist::configure($schema))
                                                                    ->fillForm(fn($record): array => $record->load(['category'])->toArray())
                                                                    ->slideOver()
                                                                    ->model(Product::class)
                                                                    ->stickyModalFooter()
                                                                    ->stickyModalHeader()
                                                            ),
                                                    ])
                                                    ->columnSpanFull()
                                            ]),
                                        ItemCard::make('Dados financeiros')
                                            ->icon(Phosphor::MoneyThin)
                                            ->extraAttributes(['class' => 'item-card--form-panel'])
                                            ->columns(3)
                                            ->schema([
                                                Money::make('total_value', 'Subtotal'),
                                                // Exibe o desconto formatado dependendo se é porcentagem ou valor fixo
                                                Money::make('discount_display')
                                                    ->label('Desconto')
                                                    ->getStateUsing(function ($record) {
                                                        if (!$record)
                                                            return 'R$ 0,00';

                                                        $discount = $record->discount ?? 0;
                                                        $type = $record->discount_type ?? 'VALUE'; // Ou o campo que define o tipo
                                            
                                                        if ($type === 'PERCENT') {
                                                            return "{$discount}%";
                                                        }

                                                        return 'R$ ' . number_format($discount, 2, ',', '.');
                                                    }),

                                                // Calcula o subtotal dinamicamente com base no tipo de desconto
                                                Money::make('subtotal', 'Total')
                                                ->hiddenLabel()
                                                    ->getStateUsing(function ($record) {
                                                        if (!$record)
                                                            return 0;


                                                        $discount = DiscountRequest::query()
                                                        ->where('deal_id', $record->id)
                                                        ->where('status', DiscountRequestStatus::APPROVED)
                                                        ->latest()
                                                        ->first();


                                                        $price = $record->total_value ?? 0;

                                                        if(!$discount){
                                                            return $price;
                                                        }

                                                        $discountValue = $record->discount ?? 0;
                                                        $discountType = $discount?->type;

                                                        if ($discountType === DiscountRequestType::PERCENT) {
                                                            // Subtotal = Preço - (Preço * (Desconto / 100))
                                                            return $price - ($price * ($discountValue / 100));
                                                        }

                                                        // Subtotal = Preço - Valor do Desconto Fixo
                                                        return max(0, $price - $discountValue);
                                                    })
                                                    ->size(TextSize::Large)
                                                    ->color(Color::Blue),
                                            ]),
                                        ItemCard::make('Informações de contato')
                                            ->icon(Phosphor::CalendarThin)
                                            ->extraAttributes(['class' => 'item-card--form-panel'])
                                            ->columns(2)
                                            ->schema([
                                                Date::make('expected_close_date', 'Previsão de Fechamento'),
                                                Date::make('actual_close_date', 'Fechamento Real'),
                                                Date::make('last_contact_date', 'Último Contato'),
                                                Text::make('loss_reason')
                                                    ->label('Motivo da Perda'),
                                                Text::make('notes')
                                                    ->label('Anotações')
                                                    ->columnSpan(2),
                                            ]),
                                    ]),
                            ]),
                        SegmentTab::make('Contatos')
                        ->badge(fn($record) => $record->notesList()->count())
                            ->badgeColor(Color::Orange)
                            ->schema([
                                Repeater::make('notesList', null, [
                                    Grid::make(2)
                                        ->schema([
                                            Text::make('interaction_type')
                                                ->label('Canal'),
                                            Date::make('contact_date', 'Data do contato', ['withTime' => false]),
                                            Text::make('content')
                                                ->label('Conteúdo')
                                                ->columnSpan(2),
                                            Date::make('next_follow_up_date', 'Próximo contato', ['withTime' => false]),
                                            Text::make('next_action')
                                                ->label('Próxima ação'),
                                        ])
                                ], ['columns' => 1])
                            ]),
                        SegmentTab::make('Solicitações')
                            ->badge(fn($record) => $record->discountRequests()->where('status', DiscountRequestStatus::PENDING)->count())
                            ->badgeColor(Color::Orange)
                            ->schema([
                                RepeatableEntry::make('discountRequests')
                                    ->getStateUsing(fn($record) => $record->discountRequests()->orderBy('created_at', 'desc')->get())
                                    ->hiddenLabel()
                                    ->extraAttributes(['class' => 'custom-clean-repeatable'])
                                    ->schema([
                                        Callout::make(function ($record) {
                                            $statusLabel = ($record?->status && method_exists($record->status, 'getLabel'))
                                                ? $record->status->getLabel()
                                                : $record?->status;
                                            // Define a cor do badge com base no status
                                            $badgeColorClass = match ($record->status) {
                                                DiscountRequestStatus::PENDING => 'bg-warning-500/10 text-warning-600 dark:text-warning-400',
                                                DiscountRequestStatus::APPROVED => 'bg-success-500/10 text-success-600 dark:text-success-400',
                                                DiscountRequestStatus::REJECTED => 'bg-danger-500/10 text-danger-600 dark:text-danger-400',
                                                default => 'bg-gray-500/10 text-gray-600 dark:text-gray-400',
                                            };

                                            $htmlTitle = "<div class='flex items-center gap-2'>" .
                                                "<span>Solicitação de desconto</span>" .
                                                "<span class='inline-flex items-center justify-center rounded-md px-2 py-0.5 text-xs font-medium {$badgeColorClass}'>{$statusLabel}</span>" .
                                                "</div>";

                                            return new HtmlString($htmlTitle);
                                        })
                                            ->color(function ($record) {
                                                return $record->status->color();
                                            })
                                            ->description(function ($record) {
                                                // Se o $record for a própria discount request, pegamos o deal e o solicitante dela:
                                                $actorName = $record->requester?->name ?? 'Sistema';

                                                $discountValueFormatted = $record->type == DiscountRequestType::VALUE
                                                    ? "R$ " . number_format($record->amount, 2, ',', '.')
                                                    : (float) $record->amount . '%';

                                                $productsList = "";
                                                if ($record->deal && method_exists($record->deal, 'products')) {
                                                    foreach ($record->deal->products as $product) {
                                                        $productName = $product->name ?? 'Produto';
                                                        $productQty = $product->pivot->quantity ?? $product->quantity ?? 1;
                                                        $productUnitPrice = "R$ " . number_format($product->pivot->unit_price, 2, ',', '.');
                                                        $productsList .= "<li>{$productName} (<span style='font-family: \"Space Mono\", monospace;'>{$productUnitPrice}</span> x {$productQty})</li>";
                                                    }
                                                }

                                                $dealTotal = $record->deal ? "R$ " . number_format($record?->deal?->total_value, 2, ',', '.') : 'R$ 0,00';

                                                $amount = match ($record->type) {
                                                    DiscountRequestType::VALUE => $record?->deal?->total_value - $record->amount,
                                                    DiscountRequestType::PERCENT => $record?->deal?->total_value - ($record?->deal?->total_value * ($record->amount / 100)),
                                                    default => $record?->deal?->total_value,
                                                };

                                                $discountedTotal = "R$ " . number_format($amount, 2, ',', '.');

                                                $html = "<b>{$actorName}</b> solicitou desconto de <span style='font-family: \"Space Mono\", monospace; font-weight:bold'>{$discountValueFormatted}</span>.<br>" .
                                                    // "<ul class='list-products-request'/>{$productsList}</ul>" .
                                                    "<div class='values-request-info'>" .
                                                    "<p >Valor total do negócio: <span>{$dealTotal}</span></p>" .
                                                    "<p>Valor com desconto: <span>{$discountedTotal}</span></p>" .
                                                    "</div>";

                                                return new HtmlString($html);
                                            })
                                            ->actions([
                                                Action::make('approve')
                                                    ->button()
                                                    ->label('Aceitar')
                                                    ->color('success')
                                                    ->icon('heroicon-o-check')
                                                    ->visible(fn($record) => $record->status === DiscountRequestStatus::PENDING)
                                                    ->requiresConfirmation()
                                                    ->action(fn($record) => DealService::approveDiscount($record->id))
                                                    ->after(function ($livewire) {
                                                        if (method_exists($livewire, 'getRecord') && $livewire->getRecord()) {
                                                            // fresh() busca do banco e substitui a instância, forçando o Livewire a atualizar a tela
                                                            $livewire->record = $livewire->getRecord()->fresh();

                                                            // Se houver relações envolvidas que mudaram de status, carregue-as novamente:
                                                            $livewire->record->load(['discountRequests', 'products']);
                                                        }

                                                        $livewire->dispatch('$refresh');
                                                    }),

                                                Action::make('reject')
                                                    ->button()
                                                    ->label('Recusar')
                                                    ->color('danger')
                                                    ->icon('heroicon-o-x-mark')
                                                    ->visible(fn($record) => $record->status === DiscountRequestStatus::PENDING)
                                                    ->requiresConfirmation()
                                                    ->action(fn($record) => DealService::rejectDiscount($record->id))
                                                    ->after(function ($livewire) {
                                                        // Se estiver em uma página de View/Edit do Filament
                                                        if (method_exists($livewire, 'getRecord') && $livewire->getRecord()) {
                                                            $livewire->getRecord()->refresh();
                                                            // Recarrega o relacionamento das solicitações para trazer o novo status do banco
                                                            $livewire->getRecord()->load('discountRequests'); // Substitua pelo nome correto da relation
                                                        }

                                                        $livewire->dispatch('$refresh');
                                                    }),
                                            ]),
                                    ])
                            ])
                            ->hidden(Auth::user()->profile === UserProfile::USER)

                    ])
            ]);
    }
}
