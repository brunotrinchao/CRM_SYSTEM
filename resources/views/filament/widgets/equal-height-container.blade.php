<x-filament::widget>
    {{-- Container Principal dividindo as duas colunas (75% e 25%) --}}
    {{-- pageFilters é repassado aos filhos: widgets aninhados via @livewire não recebem
         pageFilters automaticamente (só os filhos diretos do Dashboard recebem). --}}
    <div style="display: flex; gap: 1rem; width: 100%; align-items: flex-start;">

        {{-- COLUNA 1: 75% da largura --}}
        <div style="flex: 3; display: flex; flex-direction: column; gap: 1rem;">

            {{-- Widget 1 da Coluna 1 --}}
            <div style="flex: 1; display: flex; flex-direction: column;">
                @livewire(\App\Filament\Widgets\SalesFunnelOverview::class, ['pageFilters' => $this->pageFilters ?? null], key('sales-funnel'))
            </div>

            {{-- Widget 1 da Coluna 1 --}}
            @if (auth()->user()?->profile === \App\Enums\UserProfile::ADMIN)
            <div style="flex: 1; display: flex; flex-direction: column;">
                @livewire(\App\Filament\Widgets\RevenueEvolutionWidget::class, ['pageFilters' => $this->pageFilters ?? null], key('revenue-evolution'))
            </div>
            @endif

            {{-- Widget 2 da Coluna 1 --}}
            <div style="flex: 1; display: flex; flex-direction: column;">
                @livewire(\App\Filament\Widgets\RecentDealsWidget::class, ['pageFilters' => $this->pageFilters ?? null], key('recent-deals'))
            </div>

        </div>

        {{-- COLUNA 2: 25% da largura --}}
        <div style="flex: 1; display: flex; flex-direction: column; gap: 1rem;">

{{-- Widget 1 da Coluna 2 --}}
            <div>
                @livewire(\App\Filament\Widgets\DealsPendingContactWidget::class, [null], key('deal-pending-contact'))
            </div>

            {{-- Widget 1 da Coluna 2 --}}
            <div>
                @livewire(\App\Filament\Widgets\Reports\SellerPerformanceWidget::class, ['pageFilters' => $this->pageFilters ?? null], key('top-sellers'))
            </div>

            {{-- Widget 2 da Coluna 2 --}}
            @if (auth()->user()?->profile === \App\Enums\UserProfile::ADMIN)
                <div>
                    @livewire(\App\Filament\Widgets\CriticalStockTableWidget::class, ['pageFilters' => $this->pageFilters ?? null], key('critical-stock-table'))
                </div>
            @endif

        </div>

    </div>
</x-filament::widget>