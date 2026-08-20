<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Components\Card;
use App\Components\Infolist\Date;
use App\Components\Infolist\Repeater as RepeaterCustom;
use App\Components\Infolist\Text;
use App\Enums\ClientOrigin;
use App\Models\Address;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\SegmentTabs;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\SegmentTabs\SegmentTab;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Colors\Color;
use ToneGabes\Filament\Icons\Enums\Phosphor;

use App\Enums\DealStatus;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Deals\Schemas\DealInfolist;
use App\Models\Deal;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\ItemCard;
use Filament\Schemas\Components\Actions;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SegmentTabs::make('Tabs')
                    ->tabs([
                        SegmentTab::make('Dados do cliente')
                            ->schema([
                                Card::make()
                                    ->columns(2)
                                    ->schema([
                                        Text::make('name', 'Nome'),
                                        Text::make('email', 'E-mail'),
                                        Text::make('phone', 'Telefone', ['phone' => true]),
                                        Text::make('cellphone', 'Celular', ['phone' => true]),
                                        Text::make('origin', 'Origem', [
                                            'badge' => true,
                                        ])
                                            ->color(fn(ClientOrigin $state): string => $state->color()),
                                        Text::make('description', 'Descrição', [
                                            'columnSpanFull' => true,
                                        ]),
                                        Date::make('created_at', 'Criado em', [
                                            'withTime' => true,
                                        ]),
                                    ])
                                // Card::make('Endereços', [

                                // ], [
                                //     'icon' => Heroicon::MapPin,
                                // ]),
                            ]),
                        SegmentTab::make('Endereços')
                            ->badge(fn($record) => $record->addresses()->count())
                            ->badgeColor(Color::Gray)
                            ->schema([
                                RepeaterCustom::make('addresses', null, [
                                    TextEntry::make('full_address')
                                        ->label('Endereço Completo'),
                                ], ['columns' => 1]),
                            ]),
                        SegmentTab::make('Negócios')
                            ->badge(fn($record) => $record->deals()->count())
                            ->badgeColor(Color::Gray)
                            ->schema([
                                RepeatableEntry::make('deals')
                                    ->getStateUsing(fn($record) => $record->deals()->orderBy('created_at', 'desc')->get())
                                    ->hiddenLabel()
                                    ->extraAttributes(['class' => 'custom-clean-repeatable'])
                                    ->schema([
                                        ItemCard::make(fn (Deal $record) => $record->title)
                                            ->icon(Phosphor::Briefcase)
                                            ->extraAttributes(['class' => 'item-card--form-panel'])
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        Text::make('title', 'Negócio')
                                                            ->columnSpan(2)
                                                            ->suffixAction(
                                                                SimpleActions::getReadOnlyViewInfolist(
                                                                    width: Width::Large,
                                                                    schemaViewCallback: fn(Schema $schema) => DealInfolist::configure($schema),
                                                                    model: Deal::class,
                                                                    recordName: 'Negócio',
                                                                    modal: false,
                                                                    relations: ['client', 'products', 'discountRequests'],
                                                                )
                                                            ),
                                                         TextEntry::make('products_display')
                                                             ->label('Produtos')
                                                             ->getStateUsing(function (Deal $record) {
                                                                 $record->loadMissing('products', 'product');
                                                                 if ($record->products && $record->products->isNotEmpty()) {
                                                                     return $record->products->pluck('name')->join(', ');
                                                                 }
                                                                 if ($record->product) {
                                                                     return $record->product->name;
                                                                 }
                                                                 return '-';
                                                             }),
                                                        TextEntry::make('status')
                                                            ->label('Status')
                                                            ->badge()
                                                            ->color(fn (DealStatus $state) => $state->color()),
                                                        Text::make('total_value', 'Valor', [
                                                            'money' => true
                                                        ]),
                                                        Text::make('discount', 'Desconto', [
                                                            'money' => true
                                                        ]),
                                                        TextEntry::make('actual_close_date')
                                                            ->label('Data do ganho')
                                                            ->date('d/m/Y')
                                                            ->visible(fn($record) => $record?->status === DealStatus::WON),
                                                        TextEntry::make('loss_reason')
                                                            ->label('Motivo da perda')
                                                            ->visible(fn($record) => $record?->status === DealStatus::LOST),

                                                        TextEntry::make('id')
                                                            ->hiddenLabel()
                                                            ->formatStateUsing(fn () => '')
                                                            ->suffixActions([
                                                                SimpleActions::getReadOnlyViewInfolist(
                                                                    width: Width::Large,
                                                                    schemaViewCallback: fn(Schema $schema) => DealInfolist::configure($schema),
                                                                    model: Deal::class,
                                                                    recordName: 'Negócio',
                                                                    modal: false,
                                                                    relations: ['client', 'products', 'discountRequests'],
                                                                )
                                                            ])
                                                            ->columnSpan(2),
                                                    ])
                                            ])
                                    ])
                            ])
                    ]),
            ]);
    }
}
