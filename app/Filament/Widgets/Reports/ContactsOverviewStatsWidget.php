<?php

namespace App\Filament\Widgets\Reports;

use App\Enums\ChannelNote;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\DealNote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ContactsOverviewStatsWidget extends BaseWidget
{
    use HasDashboardScope;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $period = $this->getSelectedPeriod();
        $filters = $this->pageFilters ?? $this->filters ?? [];
        $interactionType = $filters['interaction_type'] ?? null;

        $applyProfileScope = function ($query) use ($filters) {
            if ($this->isUserScoped()) {
                return $query->where('deal_notes.user_id', \App\Filament\Pages\Dashboard::getUserId());
            }

            $userId = $filters['user_id'] ?? null;
            if (! blank($userId)) {
                return $query->where('deal_notes.user_id', $userId);
            }

            return $query;
        };

        $baseQuery = fn () => DealNote::query()
            ->whereBetween('contact_date', [$period['start'], $period['end']])
            ->tap($applyProfileScope);

        if (! blank($interactionType)) {
            $baseQuery = fn () => DealNote::query()
                ->whereBetween('contact_date', [$period['start'], $period['end']])
                ->where('interaction_type', $interactionType)
                ->tap($applyProfileScope);
        }

        // 1. Total de Contatos / Interações
        $totalContacts = $baseQuery()->count();

        // 2. Clientes Únicos Contatados
        $uniqueClients = $baseQuery()
            ->join('deals', 'deal_notes.deal_id', '=', 'deals.id')
            ->distinct()
            ->count('deals.client_id');

        // 3. Canal Mais Utilizado
        $topChannelRow = $baseQuery()
            ->selectRaw('interaction_type, COUNT(*) as count')
            ->groupBy('interaction_type')
            ->orderByDesc('count')
            ->first();

        $topChannelName = 'Sem registros';
        $topChannelCount = 0;

        if ($topChannelRow && $topChannelRow->interaction_type) {
            $channelEnum = $topChannelRow->interaction_type instanceof ChannelNote
                ? $topChannelRow->interaction_type
                : ChannelNote::tryFrom($topChannelRow->interaction_type);

            $topChannelName = $channelEnum?->getLabel() ?? $topChannelRow->interaction_type;
            $topChannelCount = $topChannelRow->count;
        }

        // 4. Próximos Retornos Agendados (A partir de hoje)
        $pendingFollowUpsQuery = DealNote::query()
            ->whereNotNull('next_follow_up_date')
            ->where('next_follow_up_date', '>=', now()->startOfDay())
            ->tap(fn ($q) => $this->scopeByProfile($q));

        if (! blank($interactionType)) {
            $pendingFollowUpsQuery->where('interaction_type', $interactionType);
        }

        $pendingFollowUpsCount = $pendingFollowUpsQuery->count();

        return [
            Stat::make('Total de Contatos', number_format($totalContacts, 0, ',', '.'))
                ->description('Interações registradas no período')
                ->descriptionIcon(Phosphor::PhoneCallDuotone)
                ->color('primary'),

            Stat::make('Clientes Contatados', number_format($uniqueClients, 0, ',', '.'))
                ->description('Clientes distintos atendidos')
                ->descriptionIcon(Phosphor::UsersDuotone)
                ->color('info'),

            Stat::make('Canal Mais Utilizado', $topChannelName)
                ->description($topChannelCount > 0 ? "{$topChannelCount} interações registradas" : 'Sem dados')
                ->descriptionIcon(Phosphor::ChatCircleTextDuotone)
                ->color('success'),

            Stat::make('Próximos Agendamentos', number_format($pendingFollowUpsCount, 0, ',', '.'))
                ->description('Retornos agendados pendentes')
                ->descriptionIcon(Phosphor::CalendarCheckDuotone)
                ->color('warning'),
        ];
    }
}
