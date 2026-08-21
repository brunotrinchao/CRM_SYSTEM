<?php

namespace App\Filament\Widgets\Reports;

use App\Enums\ChannelNote;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\DealNote;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ContactsTableWidget extends BaseWidget
{
    use HasDashboardScope;

    protected static ?string $heading = 'Histórico Detalhado de Contatos e Retornos';
    protected ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $period = $this->getSelectedPeriod();
        $filters = $this->pageFilters ?? $this->filters ?? [];
        $interactionType = $filters['interaction_type'] ?? null;

        $query = DealNote::query()
            ->with(['user', 'deal.client'])
            ->whereBetween('contact_date', [$period['start'], $period['end']])
            ->tap(fn ($q) => $this->scopeByProfile($q));

        if (! blank($interactionType)) {
            $query->where('interaction_type', $interactionType);
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('contact_date')
                    ->label('Data do Contato')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Vendedor')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('deal.client.name')
                    ->label('Cliente')
                    ->placeholder('Sem cliente')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('deal.title')
                    ->label('Negócio')
                    ->placeholder('Sem negócio')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('interaction_type')
                    ->label('Canal')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ChannelNote ? $state->getLabel() : (ChannelNote::tryFrom($state)?->getLabel() ?? $state))
                    ->color(fn ($state): string => match ($state instanceof ChannelNote ? $state->value : $state) {
                        'WHATSAPP' => 'success',
                        'CALL' => 'warning',
                        'MEETING' => 'info',
                        'EMAIL' => 'primary',
                        'VISIT' => 'secondary',
                        default => 'gray',
                    }),

                TextColumn::make('content')
                    ->label('Resumo do Retorno')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->content),

                TextColumn::make('next_follow_up_date')
                    ->label('Próximo Retorno')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Não agendado')
                    ->sortable(),

                TextColumn::make('next_action')
                    ->label('Próxima Ação')
                    ->placeholder('-')
                    ->limit(25),
            ])
            ->defaultSort('contact_date', 'desc')
            ->paginated([10, 25, 50]);
    }
}
