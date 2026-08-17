<?php

namespace App\Services;

use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Support\Carbon;

class SellerWorkloadService
{
    /**
     * Retorna a lista de vendedores (profile = USER) enriquecida com métricas de carga de trabalho, recência e histórico com o cliente.
     */
    public static function getSellersWorkload(?int $clientId = null): array
    {
        $sellers = User::query()
            ->where('profile', UserProfile::USER)
            ->with(['deals' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }])
            ->get();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return $sellers->map(function ($seller) use ($startOfMonth, $endOfMonth, $clientId) {
            $allDeals = $seller->deals;

            // Negócios ativos (Em negociação ou Pendentes)
            $activeDeals = $allDeals->filter(fn ($d) => in_array($d->status, [DealStatus::PENDING, DealStatus::NEGOTIATING]));
            $activeDealsCount = $activeDeals->count();
            $activeDealsValue = $activeDeals->sum('total_value');

            // Negócios ganhos no mês atual
            $wonDealsThisMonth = $allDeals->filter(function ($d) use ($startOfMonth, $endOfMonth) {
                return $d->status === DealStatus::WON && $d->created_at >= $startOfMonth && $d->created_at <= $endOfMonth;
            });
            $wonDealsCount = $wonDealsThisMonth->count();
            $wonDealsValue = $wonDealsThisMonth->sum('total_value');

            // Negócios já fechados/ganhos com ESTE CLIENTE ESPECÍFICO
            $closedDealsWithClientCount = 0;
            $closedDealsWithClientValue = 0.0;

            if ($clientId) {
                $clientDeals = $allDeals->filter(fn ($d) => (int) $d->client_id === (int) $clientId && $d->status === DealStatus::WON);
                $closedDealsWithClientCount = $clientDeals->count();
                $closedDealsWithClientValue = (float) $clientDeals->sum('total_value');
            }

            // Último negócio atribuído
            $lastDeal = $allDeals->sortByDesc('created_at')->first();
            $lastAssignedAt = $lastDeal?->created_at;
            $lastAssignedHuman = $lastAssignedAt ? $lastAssignedAt->diffForHumans() : 'Sem atribuição recente';

            // Iniciais do nome
            $words = array_values(array_filter(explode(' ', trim($seller->name))));
            if (count($words) >= 2) {
                $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1));
            } else {
                $initials = mb_strtoupper(mb_substr($seller->name, 0, 2));
            }

            return [
                'id' => $seller->id,
                'name' => $seller->name,
                'email' => $seller->email,
                'avatar' => $seller->avatar_url ?? $seller->avatar ?? null,
                'initials' => $initials,
                'active_deals_count' => $activeDealsCount,
                'active_deals_value' => (float) $activeDealsValue,
                'won_deals_count' => $wonDealsCount,
                'won_deals_value' => (float) $wonDealsValue,
                'closed_deals_with_client_count' => $closedDealsWithClientCount,
                'closed_deals_with_client_value' => $closedDealsWithClientValue,
                'last_assigned_at' => $lastAssignedAt,
                'last_assigned_human' => $lastAssignedHuman,
            ];
        })
        ->sortBy([
            ['closed_deals_with_client_count', 'desc'],
            ['active_deals_count', 'asc'],
            ['last_assigned_at', 'asc'],
        ])
        ->values()
        ->toArray();
    }

    
}
