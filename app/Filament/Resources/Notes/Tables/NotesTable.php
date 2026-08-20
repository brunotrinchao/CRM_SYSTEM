<?php

namespace App\Filament\Resources\Notes\Tables;

use App\Enums\ChannelNote;
use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Notes\Schemas\NotesForm;
use App\Filament\Resources\Notes\Schemas\NotesInfolist;
use App\Models\DealNote;
use App\Services\DealNoteService;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexDatePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->orderBy('created_at', 'desc');
                if (Auth::check() && Auth::user()?->profile === UserProfile::USER) {
                    $query->where('user_id', Auth::id());
                }
            })
            ->columns([
                TextColumn::make('deal.client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deal.title')
                    ->label('Negócio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('interaction_type')
                    ->label('Canal')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? $state)
                    ->color(Color::Neutral)
                    ->sortable(),
                TextColumn::make('contact_date')
                    ->label('Data do Contato')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('content')
                    ->label('Conteúdo')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('next_follow_up_date')
                    ->label('Próximo Contato')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('next_action')
                    ->label('Próxima Ação')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('client')
                    ->label('Cliente')
                    ->searchable()
                    ->preload()
                    ->relationship('deal.client', 'name', fn ($query) => (Auth::check() && Auth::user()?->profile === UserProfile::USER) ? $query->where('user_id', Auth::id()) : $query),
                SelectFilter::make('interaction_type')
                    ->label('Canal')
                    ->options(ChannelNote::class),
                SelectFilter::make('deal_id')
                    ->label('Negócio')
                    ->searchable()
                    ->preload()
                    ->relationship('deal', 'title', fn ($query) => (Auth::check() && Auth::user()?->profile === UserProfile::USER) ? $query->where('user_id', Auth::id()) : $query),
                SelectFilter::make('user_id')
                    ->label('Registrado por')
                    ->searchable()
                    ->preload()
                    ->relationship('user', 'name')
                    ->visible(fn (): bool => Auth::user()?->profile !== UserProfile::USER),
                DateRangeFilter::make('contact_date')
                    ->label('Data do Contato'),
                TrashedFilter::make()
                    ->visible(fn (): bool => Auth::user()?->profile !== UserProfile::USER),
            ])
            ->recordUrl(null)
            ->recordAction('custom_view')
            ->recordActions([
                SimpleActions::getViewWithEditAndDelete(
                    width: Width::Large,
                    schemaCallback: fn ($schema) => NotesForm::configure($schema, isDealForm: false),
                    actionCallback: fn (Model $record, array $data) => DealNoteService::update($record, $data),
                    model: DealNote::class,
                    schemaViewCallback: fn (Schema $schema) => NotesInfolist::configure($schema),
                    recordName: 'Contato',
                    recordAction: fn (DealNote $record): bool => (Auth::user()?->profile !== UserProfile::USER || $record->user_id === Auth::id())
                        && in_array($record->deal?->status, [DealStatus::PENDING, DealStatus::NEGOTIATING], true),
                    deleteAction: fn (DealNote $record): bool => Auth::user()?->profile !== UserProfile::USER || $record->user_id === Auth::id(),
                    relations: ['deal', 'deal.client', 'user'],
                )
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
