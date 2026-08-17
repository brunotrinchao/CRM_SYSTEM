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
                                    ->hiddenLabel()
                                    ->schema([
                                        Text::make('title', 'Negócio', [
                                            'columnSpanFull' => true,
                                        ]),
                                        TextEntry::make('product.name')
                                            ->label('Produto'),
                                        TextEntry::make('status')
                                            ->label('Status')
                                            ->badge(),
                                        Text::make('total_value', 'Valor', [
                                            'money' => true
                                        ]),
                                        Text::make('discount', 'Desconto', [
                                            'money' => true
                                        ]),
                                        TextEntry::make('expected_close_date')
                                            ->label('Fechamento previsto')
                                            ->date('d/m/Y'),
                                    ])
                                    ->columns(2)
                            ])
                    ]),
            ]);
    }
}
