<?php

namespace App\Filament\Widgets\Reports;

use App\Enums\ChannelNote;
use App\Enums\UserProfile;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\DealNote;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SellerContactsRankingWidget extends BaseWidget
{
    use HasDashboardScope;

    protected static ?string $heading = 'Desempenho de Contatos por Vendedor';
    protected ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $period = $this->getSelectedPeriod();
        $filters = $this->pageFilters ?? $this->filters ?? [];
        $selectedUserId = $filters['user_id'] ?? null;
        $interactionType = $filters['interaction_type'] ?? null;

        $sellersQuery = User::query()
            ->where('profile', UserProfile::USER);

        if ($this->isUserScoped()) {
            $sellersQuery->where('id', auth()->id());
        } elseif (! blank($selectedUserId)) {
            $sellersQuery->where('id', $selectedUserId);
        }

        return $table
            ->query($sellersQuery)
            ->columns([
                TextColumn::make('name')
                    ->label('Vendedor')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_contacts')
                    ->label('Total de Contatos')
                    ->state(function (User $record) use ($period, $interactionType): int {
                        $query = DealNote::query()
                            ->where('user_id', $record->id)
                            ->whereBetween('contact_date', [$period['start'], $period['end']]);

                        if (! blank($interactionType)) {
                            $query->where('interaction_type', $interactionType);
                        }

                        return $query->count();
                    })
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('whatsapp_contacts')
                    ->label('WhatsApp')
                    ->state(function (User $record) use ($period): int {
                        return DealNote::query()
                            ->where('user_id', $record->id)
                            ->where('interaction_type', ChannelNote::WHATSAPP)
                            ->whereBetween('contact_date', [$period['start'], $period['end']])
                            ->count();
                    })
                    ->badge()
                    ->color('success'),

                TextColumn::make('call_contacts')
                    ->label('Ligações')
                    ->state(function (User $record) use ($period): int {
                        return DealNote::query()
                            ->where('user_id', $record->id)
                            ->where('interaction_type', ChannelNote::CALL)
                            ->whereBetween('contact_date', [$period['start'], $period['end']])
                            ->count();
                    })
                    ->badge()
                    ->color('warning'),

                TextColumn::make('meeting_contacts')
                    ->label('Reuniões / Visitas')
                    ->state(function (User $record) use ($period): int {
                        return DealNote::query()
                            ->where('user_id', $record->id)
                            ->whereIn('interaction_type', [ChannelNote::MEETING, ChannelNote::VISIT])
                            ->whereBetween('contact_date', [$period['start'], $period['end']])
                            ->count();
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('unique_clients')
                    ->label('Clientes Atendidos')
                    ->state(function (User $record) use ($period): int {
                        return DealNote::query()
                            ->where('deal_notes.user_id', $record->id)
                            ->whereBetween('contact_date', [$period['start'], $period['end']])
                            ->join('deals', 'deal_notes.deal_id', '=', 'deals.id')
                            ->distinct()
                            ->count('deals.client_id');
                    })
                    ->badge()
                    ->color('gray'),
            ])
            ->paginated([5, 10, 25])
            ->defaultSort('name');
    }
}
