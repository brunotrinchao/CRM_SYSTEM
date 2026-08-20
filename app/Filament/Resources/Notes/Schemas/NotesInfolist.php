<?php

namespace App\Filament\Resources\Notes\Schemas;

use App\Components\Infolist\Date;
use App\Components\Infolist\Text;
use App\Models\DealNote;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\ItemCard;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class NotesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ItemCard::make(fn (DealNote $record) => "Contato - " . ($record->contact_date?->format('d/m/Y') ?? 'Sem data'))
                    ->icon(Phosphor::ChatCircleText)
                    ->extraAttributes(['class' => 'item-card--form-panel'])
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Text::make('interaction_type')
                                    ->label('Canal')
                                    ->getStateUsing(fn (DealNote $record) => $record->interaction_type?->getLabel() ?? $record->interaction_type),

                                Date::make('contact_date', 'Data do contato', ['withTime' => false]),

                                TextEntry::make('deal.client.name')
                                    ->label('Cliente')
                                    ->columnSpan(2),

                                TextEntry::make('deal.title')
                                    ->label('Negócio')
                                    ->columnSpan(2),

                                Text::make('content')
                                    ->label('Conteúdo')
                                    ->columnSpan(2),

                                Date::make('next_follow_up_date', 'Próximo contato', ['withTime' => false]),

                                Text::make('next_action')
                                    ->label('Próxima ação'),

                                TextEntry::make('user.name')
                                    ->label('Registrado por')
                                    ->columnSpan(2),
                            ])
                    ])
            ]);
    }
}
