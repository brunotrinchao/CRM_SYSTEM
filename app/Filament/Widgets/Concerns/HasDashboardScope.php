<?php

namespace App\Filament\Widgets\Concerns;

use App\Enums\UserProfile;
use App\Filament\Pages\Dashboard;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Livewire\Attributes\On;

trait HasDashboardScope
{
    use InteractsWithPageFilters;

    #[On('page-filters-updated')]
    public function onPageFiltersUpdated(?array $filters = null): void
    {
        if ($filters !== null) {
            $this->pageFilters = $filters;
        }
    }

    /**
     * Retorna true quando o usuário logado tem perfil USER (dados próprios).
     */
    protected function isUserScoped(): bool
    {
        return Dashboard::getUserProfile() === UserProfile::USER->value;
    }

    /**
     * Aplica o escopo de perfil ou vendedor selecionado no filtro:
     * - USER → apenas os próprios dados (user_id = auth)
     * - ADMIN/MANAGER → dados do vendedor selecionado no filtro ou todos se vazio
     */
    protected function scopeByProfile($query)
    {
        if ($this->isUserScoped()) {
            return $query->where('user_id', Dashboard::getUserId());
        }

        $filters = $this->pageFilters ?? $this->filters ?? [];
        $userId = $filters['user_id'] ?? null;

        if (! blank($userId)) {
            return $query->where('user_id', $userId);
        }

        return $query;
    }

    /**
     * Aplica o filtro de status selecionado na página de relatórios.
     */
    protected function scopeByStatus($query)
    {
        $filters = $this->pageFilters ?? $this->filters ?? [];
        $status = $filters['status'] ?? null;

        if (! blank($status)) {
            return $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Aplica o filtro de período vindo do dashboard (FlexDateRangeField).
     * Suporta string "d/m/Y - d/m/Y" ou array ['start' => ..., 'end' => ...].
     */
    protected function scopeByPeriod($query, ?string $column = 'created_at')
    {
        $period = $this->getSelectedPeriod();

        $query->whereBetween($column, [$period['start'], $period['end']]);

        return $query;
    }

    /**
     * Normaliza o valor de period_range do dashboard para um array Carbon.
     *
     * Formatos suportados:
     * - string: "01/08/2026 até 31/08/2026" (d/m/Y)  → DateRangePicker (separador configurável)
     * - string: "01/08/2026 - 31/08/2026"            → FlexDateRangeField
     * - array:  ['start' => '2026-08-01', 'end' => '2026-08-31'] (Y-m-d)
     *
     * @return array{start: \Illuminate\Support\Carbon, end: \Illuminate\Support\Carbon}|null
     */
    protected function parsePeriodRange(mixed $range): ?array
    {
        // Formato array: ['start' => ..., 'end' => ...]
        if (is_array($range)) {
            $start = $range['start'] ?? null;
            $end = $range['end'] ?? null;

            if (blank($start) || blank($end)) {
                return null;
            }

            return [
                'start' => \Illuminate\Support\Carbon::parse($start)->startOfDay(),
                'end' => \Illuminate\Support\Carbon::parse($end)->endOfDay(),
            ];
        }

        // Formato string: separador pode ser " - " ou " até " (DateRangePicker)
        if (is_string($range)) {
            $range = trim($range);
            if ($range === '') {
                return null;
            }

            // Separa no primeiro separador conhecido. Datas d/m/Y não contêm hífen,
            // então "-" é seguro. O "até" do DateRangePicker também é tratado.
            $separators = [' até ', ' - ', ' to ', '~', ','];
            $parts = null;
            foreach ($separators as $separator) {
                if (str_contains($range, $separator)) {
                    $parts = array_map('trim', explode($separator, $range, 2));
                    break;
                }
            }

            // Último recurso: quebrar por espaço ("01/08/2026 31/08/2026") ou por "/" de range no meio
            if ($parts === null && preg_match('/(\d{1,2}\/\d{1,2}\/\d{4})\s+(\d{1,2}\/\d{1,2}\/\d{4})/', $range, $m)) {
                $parts = [$m[1], $m[2]];
            }

            if ($parts === null) {
                return null;
            }

            $start = $parts[0] ?? null;
            $end = $parts[1] ?? null;

            if (blank($start) || blank($end)) {
                return null;
            }

            $startDate = \Illuminate\Support\Carbon::createFromFormat('d/m/Y', $start);
            $endDate = \Illuminate\Support\Carbon::createFromFormat('d/m/Y', $end);

            if (! $startDate || ! $endDate) {
                return null;
            }

            return [
                'start' => $startDate->startOfDay(),
                'end' => $endDate->endOfDay(),
            ];
        }

        return null;
    }

    /**
     * Retorna o período selecionado no dashboard, ou o mês atual como fallback.
     *
     * @return array{start: \Illuminate\Support\Carbon, end: \Illuminate\Support\Carbon}
     */
    protected function getSelectedPeriod(): array
    {
        $filters = $this->pageFilters ?? $this->filters ?? [];
        $startDate = $filters['startDate'] ?? null;
        $endDate = $filters['endDate'] ?? null;

        if (! blank($startDate) && ! blank($endDate)) {
            return [
                'start' => \Illuminate\Support\Carbon::parse($startDate)->startOfDay(),
                'end' => \Illuminate\Support\Carbon::parse($endDate)->endOfDay(),
            ];
        }

        $range = $filters['period_range'] ?? null;
        $period = $this->parsePeriodRange($range);

        return $period ?? [
            'start' => now()->startOfMonth(),
            'end' => now()->endOfMonth(),
        ];
    }

    /**
     * Retorna a descrição formatada do período selecionado para títulos de widgets.
     */
    protected function getPeriodTitleSuffix(): string
    {
        $period = $this->getSelectedPeriod();
        $diffInDays = (int) $period['start']->diffInDays($period['end']);

        if ($diffInDays < 30) {
            if ($period['start']->format('Y-m') === $period['end']->format('Y-m')) {
                return ' (' . ucfirst($period['start']->translatedFormat('F \d\e Y')) . ')';
            }

            return ' (' . $period['start']->format('d/m/Y') . ' até ' . $period['end']->format('d/m/Y') . ')';
        }

        return ' (' . ucfirst($period['start']->translatedFormat('M/Y')) . ' até ' . ucfirst($period['end']->translatedFormat('M/Y')) . ')';
    }

    /**
     * Retorna o período anterior de mesma duração do período selecionado.
     *
     * @return array{start: \Illuminate\Support\Carbon, end: \Illuminate\Support\Carbon}
     */
    protected function getPreviousPeriod(array $period): array
    {
        // Carbon retorna diff assinado (negativo quando $end > $start); usa abs para robustez
        $duration = abs($period['end']->diffInSeconds($period['start']));

        return [
            'start' => $period['start']->copy()->subSeconds($duration + 1)->startOfDay(),
            'end' => $period['start']->copy()->subSecond()->endOfDay(),
        ];
    }

    /**
     * Calcula o trend (%) comparando o valor do período atual com o período anterior de igual duração.
     */
    protected function calculateTrend(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
