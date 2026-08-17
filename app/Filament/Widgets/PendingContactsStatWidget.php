<?php

namespace App\Filament\Widgets;

use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Models\Deal;
use Illuminate\Support\Facades\Auth;
use LaBoiteACode\FilamentDashboardWidgets\Data\Metric;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\MetricWidget;

class PendingContactsStatWidget extends MetricWidget
{
    protected function getMetric(): Metric
    {
        $user = Auth::user();

        $query = Deal::query()
            ->whereIn('status', [DealStatus::PENDING, DealStatus::NEGOTIATING])
            ->with(['notesList' => fn ($q) => $q->orderBy('created_at', 'desc')]);

        if ($user?->profile === UserProfile::USER) {
            $query->where('user_id', $user->id);
        } else {
            $query->whereHas('user', fn ($q) => $q->where('profile', UserProfile::USER));
        }

        $now = now();

        $pendingDeals = $query->get()->filter(function ($deal) use ($now) {
            $latestNote = $deal->notesList->first();
            $nextContact = $latestNote?->next_follow_up_date;

            if (! $nextContact) {
                return true;
            }

            return $nextContact->isPast() || $nextContact->isToday();
        });

        $count = $pendingDeals->count();

        // Contagem de atrasados há mais de 24h
        $overdue24hCount = $pendingDeals->filter(function ($deal) use ($now) {
            $latestNote = $deal->notesList->first();
            $nextContact = $latestNote?->next_follow_up_date;

            if ($nextContact && $nextContact->isPast() && $nextContact->diffInHours($now) >= 24) {
                return true;
            }

            return false;
        })->count();

        $description = $overdue24hCount > 0
            ? "{$overdue24hCount} contato(s) em atraso crítico (> 24h)"
            : "Contatos com previsão de retorno pendente";

        return Metric::make('Contatos Pendentes', $count)
            ->description($description)
            ->icon('heroicon-o-phone-arrow-up-right')
            ->color($overdue24hCount > 0 ? 'danger' : 'warning');
    }
}
