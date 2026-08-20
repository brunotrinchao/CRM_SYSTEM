<?php

namespace App\Filament\Resources\Deals\Schemas;

use App\Enums\DealStatus;
use App\Components\Card;
use App\Components\Form\DatePicker;
use App\Components\Form\NumberInput;
use App\Components\Form\Select;
use App\Components\Form\TextInput;
use App\Components\Form\Textarea;
use App\Components\Form\Toggle;
use App\Components\Repeater;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use App\Filament\Resources\Notes\Schemas\NotesForm;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Models\Product;
use App\Services\ClientService;
use App\Services\ProductService;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexTextInput;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\NumberStepper;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\SegmentTabs;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\SegmentTabs\SegmentTab;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater as FilamentRepeater;
use Filament\Forms\Components\Select as ComponentsSelect;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DealForm
{
    public static function getSteps(): array
    {
        return [
            Step::make('Dados')
                 ->schema([
                        Select::make('status', 'Status do Negócio', [
                            'options' => DealStatus::options(),
                        ])
                            ->native(false)
                            ->live()
                            ->visible(function ($record) {
                                if (! $record || ! $record->exists) {
                                    return false;
                                }
                                return in_array($record->status, [
                                    DealStatus::PENDING,
                                    DealStatus::NEGOTIATING,
                                    DealStatus::WON,
                                    DealStatus::LOST,
                                ]);
                            }),
                        Toggle::make('confirm_status_cancelled', 'Confirmar Cancelamento do Negócio')
                            ->helperText('⚠️ ATENÇÃO: O cancelamento do negócio NÃO poderá ser desfeito.')
                            ->onColor('danger')
                            ->visible(fn ($get) => $get('status') === DealStatus::CANCELLED->value || $get('status') === DealStatus::CANCELLED)
                            ->rules([
                                fn ($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $status = $get('status');
                                    $isCancelled = $status === DealStatus::CANCELLED->value || $status === DealStatus::CANCELLED;
                                    if ($isCancelled && ! $value) {
                                        $fail('Você precisa ativar a confirmação de cancelamento para continuar.');
                                    }
                                },
                            ])
                            ->dehydrated(true),
                        Toggle::make('confirm_status_lost', 'Confirmar marcação como Perdido')
                            ->helperText('Tem certeza de que deseja alterar o status deste negócio para Perdido?')
                            ->onColor('warning')
                            ->visible(fn ($get) => $get('status') === DealStatus::LOST->value || $get('status') === DealStatus::LOST)
                            ->rules([
                                fn ($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $status = $get('status');
                                    $isLost = $status === DealStatus::LOST->value || $status === DealStatus::LOST;
                                    if ($isLost && ! $value) {
                                        $fail('Você precisa ativar a confirmação para alterar o status para Perdido.');
                                    }
                                },
                            ])
                            ->dehydrated(true),
                        Select::make('client_id', 'Cliente', [
                            'relationship' => ['client', 'name'],
                            'required' => true,
                        ])
                            ->live()
                            ->getOptionLabelFromRecordUsing(fn(\App\Models\Client $record): string => "{$record->name} (" . ($record->email ?? 'Sem e-mail') . ")")
                            ->disabled(function ($record) {
                                if (! $record || ! $record->exists) {
                                    return false;
                                }
                                return $record->status !== DealStatus::PENDING;
                            })
                            ->helperText(function ($record) {
                                if ($record && $record->status !== DealStatus::PENDING) {
                                    return 'O cliente só pode ser alterado quando o negócio estiver com status Pendente.';
                                }
                                return null;
                            })
                            ->suffixAction(
                                SimpleActions::getCreateModal(
                                    width: Width::Large,
                                    schemaCallback: fn($schema) => ClientForm::configure($schema),
                                    actionCallback: fn(array $data) => ClientService::create($data),
                                    recordName: 'Cliente',
                                    modal: false,
                                    model: \App\Models\Client::class,
                                    afterCreate: function (\Illuminate\Database\Eloquent\Model $record, $livewire) {
                                        // Seta o campo client_id no form do deal com o cliente recém-criado
                                        SimpleActions::setFieldOnParentForm($livewire, 'client_id', $record->id);
                                    },
                                    name: 'create_client_modal'
                                )
                            ),
                        ComponentsSelect::make('user_id')
                            ->label('Vendedor Responsável')
                            ->native(false)
                            // ->default(fn () => \Illuminate\Support\Facades\Auth::id())
                            ->hidden(fn () => \Illuminate\Support\Facades\Auth::user()?->profile === \App\Enums\UserProfile::USER)
                            ->relationship(
                                'user',
                                'name',
                                fn($query) => $query->where('profile', \App\Enums\UserProfile::USER),
                            )
                            ->required(fn () => \Illuminate\Support\Facades\Auth::user()?->profile !== \App\Enums\UserProfile::USER)
                            ->disabled(fn($get) => blank($get('client_id')))
                            ->helperText(fn($get) => blank($get('client_id')) ? 'Selecione o cliente primeiro para liberar a escolha do vendedor.' : null)
                            ->suffixAction(
                                Action::make('compare_sellers')
                                    ->label('Comparar Carga dos Vendedores')
                                    ->icon(Phosphor::UsersThree)
                                    ->slideOver()
                                    ->disabled(fn($get) => blank($get('client_id')))
                                    ->modalSubmitAction(false)
                                    ->modalCancelActionLabel('Fechar')
                                    ->modalContent(function ($livewire, $get) {
                                        return view(
                                            'filament.forms.components.seller-assignment-assistant',
                                            [
                                                'livewire' => $livewire,
                                                'clientId' => $get('client_id'),
                                            ]
                                        );
                                    })
                                    ->action(function (Set $set, array $arguments): void {
                                        if (isset($arguments['sellerId'])) {
                                            $set('user_id', (int) $arguments['sellerId']);
                                        }
                                    })
                            ),
                        TextInput::make('title', 'Título do Negócio', [
                            'required' => true,
                            'maxLength' => 255,
                            'prefixIcon' => Heroicon::HandRaised,
                        ]),
                        DatePicker::make('actual_close_date', 'Data do Ganho')
                            ->visible(fn ($get) => $get('status') === DealStatus::WON->value || $get('status') === DealStatus::WON),
                        Textarea::make('loss_reason', 'Motivo da Perda', [
                            'rows' => 3,
                            'placeholder' => 'Escreva o motivo da perda...',
                        ])
                            ->visible(fn ($get) => $get('status') === DealStatus::LOST->value || $get('status') === DealStatus::LOST),
                        Textarea::make('notes', 'Anotações', [
                            'rows' => 3,
                            'speechDictation' => false,
                            'emojiPicker' => false,
                            'characterCounter' => false,
                            'placeholder' => 'Escreva anotações sobre o negócio...',
                        ]),
                    ]),
            Step::make('Produtos')
                ->schema([
                        Repeater::make('products')
                            ->reorderable(false)
                            ->hiddenLabel()
                            ->collapsible()
                            ->disabled(function ($record) {
                                if (! $record || ! $record->exists) {
                                    return false;
                                }
                                return ! in_array($record->status, [DealStatus::PENDING, DealStatus::NEGOTIATING]);
                            })
                            ->afterStateHydrated(function (FilamentRepeater $component, mixed $state): void {
                                if (!is_array($state) || empty($state)) {
                                    return;
                                }

                                $transformed = [];

                                foreach (array_values($state) as $item) {
                                    if (!is_array($item)) {
                                        continue;
                                    }

                                    if (array_key_exists('pivot', $item)) {
                                        // Formato vindo de $record->load('products')->toArray()
                                        $pivot = $item['pivot'] ?? [];
                                        $transformed[] = [
                                            'product_id' => $item['id'] ?? null,
                                            'quantity' => $pivot['quantity'] ?? 1,
                                            'unit_price' => $pivot['unit_price'] ?? 0,
                                            'total_price' => $pivot['total_price'] ?? 0,
                                        ];
                                    } else {
                                        // Já no formato correto (item adicionado pelo usuário no form)
                                        $transformed[] = $item;
                                    }
                                }

                                $component->state($transformed);
                            })
                            ->label('Produtos do Negócio')
                            ->itemLabel(function (array $state): ?string {
                                // Busca o nome do produto selecionado no banco com base no product_id do estado atual
                                if (!isset($state['product_id'])) {
                                    return null;
                                }

                                $product = Product::find($state['product_id']);

                                return $product ? $product->name : null;
                            })
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produto')
                                    ->native(true)
                                    ->options(Product::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $product = Product::find($state);
                                        $price = $product?->price ?? 0;

                                        $set('unit_price', $price);
                                        $set('total_price', round($price * (float) ($get('quantity') ?? 1), 2));
                                    })
                                    ->suffixAction(
                                        SimpleActions::getCreateModal(
                                            width: Width::Large,
                                            schemaCallback: fn($schema) => ProductForm::configure($schema),
                                            actionCallback: fn(array $data) => ProductService::create($data),
                                            recordName: 'Produto',
                                            modal: false,
                                            model: Product::class,
                                            name: 'create_product_modal',
                                            afterCreate: function (\Illuminate\Database\Eloquent\Model $record, $livewire) {
                                                // Atualiza o contexto se necessário após criar um novo produto
                                            }
                                        ),
                                    )
                                    ->columnSpanFull(),
                                Grid::make(2)
                                    ->schema([
                                        FlexTextInput::make('quantity')
                                            ->label('Quantidade')
                                            ->size('lg')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(99)
                                            ->default(1)
                                            ->live()
                                            ->afterStateUpdated(fn($state, $set, $get) => $set('total_price', round((float) ($get('unit_price') ?? 0) * (float) ($state ?? 1), 2)))
                                            ->required(),

                                        TextInput::make('unit_price')
                                            ->extraAttributes(['style' => 'display:none;'])
                                            ->live(),

                                        FlexTextInput::make('total_price')
                                            ->label('Subtotal (R$)')
                                            ->size('lg')
                                            ->readOnly()
                                            ->prefix('R$')
                                            ->live()
                                            ->formatStateUsing(
                                                fn($state) => number_format(round((float) $state, 2), 2, ',', '.')
                                            ),
                                    ])
                                    ->columns(2)
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Adicionar produto')
                                        ]),
            Step::make('Contato')
                ->schema(NotesForm::getComponents(isDealForm: true))
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make(static::getSteps())
            ]);
    }
}
