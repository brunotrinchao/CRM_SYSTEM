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

use App\Models\DealNote;
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

        $schema[] = \Filament\Schemas\Components\Actions::make([
            \Filament\Actions\Action::make('view_contact_history')
                ->label('Ver Histórico de Contatos')
                ->icon(\ToneGabes\Filament\Icons\Enums\Phosphor::ClockCounterClockwiseDuotone)
                ->color(\Filament\Support\Colors\Color::Blue)
                ->button()
                ->modalHeading('Histórico de Contatos do Negócio')
                ->modalWidth(\Filament\Support\Enums\Width::Large)
                ->slideOver()
                ->modalSubmitAction(false)
                ->visible(fn ($get, $record) => ! empty($get('deal_id')) || ($record && ! empty($record->deal_id)))
                ->infolist(fn ($get, $record) => [
                    \Filament\Infolists\Components\RepeatableEntry::make('notesHistory')
                        ->hiddenLabel()
                        ->getStateUsing(function () use ($get, $record) {
                            $dealId = $get('deal_id') ?? $record?->deal_id;
                            if (! $dealId) {
                                return [];
                            }

                            return DealNote::query()
                                ->with(['user', 'deal.client'])
                                ->where('deal_id', $dealId)
                                ->orderBy('contact_date', 'desc')
                                ->get();
                        })
                        ->schema([
                            \Filament\Infolists\Components\TextEntry::make('contact_date')
                                ->label('Data do Contato')
                                ->dateTime('d/m/Y H:i'),
                            \Filament\Infolists\Components\TextEntry::make('user.name')
                                ->label('Vendedor'),
                            \Filament\Infolists\Components\TextEntry::make('interaction_type')
                                ->label('Canal')
                                ->badge()
                                ->formatStateUsing(fn ($state) => $state instanceof ChannelNote ? $state->getLabel() : (ChannelNote::tryFrom($state)?->getLabel() ?? $state)),
                            \Filament\Infolists\Components\TextEntry::make('content')
                                ->label('Resumo do Contato'),
                            \Filament\Infolists\Components\TextEntry::make('next_follow_up_date')
                                ->label('Próximo Contato Agendado')
                                ->dateTime('d/m/Y H:i')
                                ->placeholder('Não agendado'),
                            \Filament\Infolists\Components\TextEntry::make('next_action')
                                ->label('Próxima Ação')
                                ->placeholder('-'),
                        ])
                ])
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
