<?php

namespace App\Filament\Resources\Notes\Schemas;

use App\Components\Form\Select;
use App\Components\Form\Textarea;
use App\Components\Form\TextInput;
use App\Enums\ChannelNote;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexDatePicker;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexTextInput;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\SelectField;
use Filament\Schemas\Schema;

use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\ItemCard;
use Bjanczak\FilamentFlexFields\Filament\Schemas\Components\ItemCardStack;


class NotesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ItemCardStack::make()
                    ->stackGap('lg')
                    // ->extraAttributes(['class' => 'fff-form-layout fff-form-layout--wide'])
                    ->schema([
                        SelectField::make('interaction_type')
                            ->label('Canal')
                            ->required()
                                    ->size('lg')
                            ->columnSpanFull()
                            ->options(ChannelNote::class),
                        FlexDatePicker::make('contact_date')
                            ->label('Data do contato')
                                    ->size('lg')
                            ->required(),
                        Textarea::make('content', 'Conteúdo', [
                            'columnSpanFull' => true,
                            'speechDictation' => true,
                            'emojiPicker' => true,
                        ]),
                        ItemCard::make('Proxímo contato')
                            ->description('Informação de um proximo contato')
                            ->extraAttributes(['class' => 'item-card--form-panel'])
                            ->schema([
                                FlexDatePicker::make('next_follow_up_date')
                                    ->label('Contato')
                                    ->required()
                                    ->withRecommendedDefaults()
                                    ->size('lg')
                                    ->columnSpanFull(),
                                FlexTextInput::make('next_action')
                                ->label('Ação')
                                    ->size('lg')
                                    ->columnSpanFull()
                            ])
                    ])
            ]);
    }
}
