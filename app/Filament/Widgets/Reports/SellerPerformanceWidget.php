<?php

namespace App\Filament\Widgets\Reports;

use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\User;
use Filament\Widgets\Widget as BaseWidget;

class SellerPerformanceWidget extends BaseWidget
{
    use HasDashboardScope;

    protected string $view = 'filament.widgets.reports.seller-ranking-widget';
    protected ?string $pollingInterval = null;

    public function getRankingData(): array
    {
        $period = $this->getSelectedPeriod();
        $filters = $this->pageFilters ?? $this->filters ?? [];

        $users = User::query()
            ->where('profile', UserProfile::USER)
            ->when(! empty($filters['user_id']), fn ($q, $uId) => $q->where('id', $uId))
            ->withCount(['deals' => function ($q) use ($period) {
                $q->where('status', DealStatus::WON)
                    ->whereBetween('created_at', [$period['start'], $period['end']])
                    ->tap(fn ($sq) => $this->scopeByStatus($sq));
            }])
            ->withSum(['deals' => function ($q) use ($period) {
                $q->where('status', DealStatus::WON)
                    ->whereBetween('created_at', [$period['start'], $period['end']])
                    ->tap(fn ($sq) => $this->scopeByStatus($sq));
            }], 'total_value')
            ->get()
            ->sortByDesc('deals_sum_total_value')
            ->values();

        $sellers = $users->map(function ($user, $index) {
            $name = $user->name;
            $words = array_values(array_filter(explode(' ', trim($name))));
            if (count($words) >= 2) {
                $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1));
            } else {
                $initials = mb_strtoupper(mb_substr($name, 0, 2));
            }

            return [
                'rank' => $index + 1,
                'id' => $user->id,
                'name' => $name,
                'avatar' => $user->avatar_url ?? $user->avatar ?? null,
                'initials' => $initials,
                'deals_count' => (int) ($user->deals_count ?? 0),
                'total_value' => (float) ($user->deals_sum_total_value ?? 0),
            ];
        });

        $top3 = $sellers->take(3)->values()->toArray();
        $others = $sellers->slice(3)->values()->toArray();

        return [
            'top3' => $top3,
            'others' => $others,
        ];
    }
}
