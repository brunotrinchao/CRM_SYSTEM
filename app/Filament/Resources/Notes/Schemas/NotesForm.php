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
    public static function getComponents(bool $isDealForm = false): array
    {
        $schema = [];

        if (! $isDealForm) {
            $schema[] = Select::make('deal_id', 'Negócio', [
                'searchable' => true,
                'preload' => true,
                'columnSpanFull' => true,
                'required' => true,
                'relationship' => [
                    'deal',
                    'title',
                    fn ($query) => (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()?->profile === \App\Enums\UserProfile::USER)
                        ? $query->where('user_id', \Illuminate\Support\Facades\Auth::id())
                        : $query
                ]
            ])
                ->size('lg');
        }

        $schema[] = SelectField::make('interaction_type')
            ->label('Canal')
            ->size('lg')
            ->columnSpanFull()
            ->required($isDealForm)
            ->options(ChannelNote::class);

        $schema[] = FlexDatePicker::make('contact_date')
            ->label('Data do contato')
            ->size('lg')
            ->required($isDealForm);

        $schema[] = Textarea::make('content', 'Conteúdo', [
            'columnSpanFull' => true,
            'speechDictation' => true,
            'emojiPicker' => true,
        ])
            ->required($isDealForm);

        $schema[] = ItemCard::make('Próximo contato')
            ->description('Informação de um próximo contato')
            ->extraAttributes(['class' => 'item-card--form-panel'])
            ->schema([
                FlexDatePicker::make('next_follow_up_date')
                    ->label('Contato')
                    ->withRecommendedDefaults()
                    ->size('lg')
                    ->columnSpanFull()
                    ->required($isDealForm),
                FlexTextInput::make('next_action')
                    ->label('Ação')
                    ->size('lg')
                    ->columnSpanFull()
            ]);

        return [
            ItemCardStack::make()
                ->stackGap('lg')
                ->schema($schema)
        ];
    }

    public static function configure(Schema $schema, bool $isDealForm = false): Schema
    {
        return $schema
            ->components(static::getComponents($isDealForm));
    }
}
