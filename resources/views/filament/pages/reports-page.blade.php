<x-filament-panels::page>

    {{-- Navegação por Abas (Tabs) --}}
    <div class="mb-4">
        <x-filament::tabs label="Módulos de Relatórios">
            <x-filament::tabs.item
                :active="$activeTab === 'vendas'"
                wire:click="setActiveTab('vendas')"
                icon="heroicon-o-chart-bar"
            >
                Vendas
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'produtos'"
                wire:click="setActiveTab('produtos')"
                icon="heroicon-o-cube"
            >
                Produtos & Estoque
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'contatos'"
                wire:click="setActiveTab('contatos')"
                icon="heroicon-o-chat-bubble-left-right"
            >
                Contatos & Retornos
            </x-filament::tabs.item>
        </x-filament::tabs>
    </div>

    {{-- Conteúdo da Aba 1: Vendas --}}
    @if ($activeTab === 'vendas')

    <x-filament::section class="mb-4">
        <form wire:change="updatedFilters">
            {{ $this->filtersForm }}
        </form>
    </x-filament::section>
    
        <div class="space-y-4">
            @livewire(\App\Filament\Widgets\Reports\SalesOverviewStatsWidget::class, ['pageFilters' => $this->filters], key('sales-stats-' . md5(json_encode($this->filters))))

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    @livewire(\App\Filament\Widgets\Reports\MonthlyRevenueChartWidget::class, ['pageFilters' => $this->filters], key('sales-chart-' . md5(json_encode($this->filters))))
                </div>
                <div class="lg:col-span-1">
                    @livewire(\App\Filament\Widgets\Reports\SellerPerformanceWidget::class, ['pageFilters' => $this->filters], key('sellers-ranking-' . md5(json_encode($this->filters))))
                </div>
            </div>

            @livewire(\App\Filament\Widgets\Reports\DealsByStatusWidget::class, ['pageFilters' => $this->filters], key('deals-status-' . md5(json_encode($this->filters))))

            @livewire(\App\Filament\Widgets\Reports\ClosedDealsTableWidget::class, ['pageFilters' => $this->filters], key('closed-deals-table-' . md5(json_encode($this->filters))))
        </div>
    @endif

    {{-- Conteúdo da Aba 2: Produtos & Estoque --}}
    @if ($activeTab === 'produtos')
        <div class="space-y-6">
            @livewire(\App\Filament\Widgets\Reports\StockOverviewStatsWidget::class, key('stock-stats'))

            @livewire(\App\Filament\Widgets\Reports\ProductsStockTableWidget::class, key('products-stock-table'))
        </div>
    @endif

    {{-- Conteúdo da Aba 3: Contatos & Retornos --}}
    @if ($activeTab === 'contatos')
        <x-filament::section class="mb-4">
            <form wire:change="updatedFilters">
                {{ $this->filtersForm }}
            </form>
        </x-filament::section>

        <div class="space-y-6">
            @livewire(\App\Filament\Widgets\Reports\ContactsOverviewStatsWidget::class, ['pageFilters' => $this->filters], key('contacts-stats-' . md5(json_encode($this->filters))))

            @livewire(\App\Filament\Widgets\Reports\SellerContactsRankingWidget::class, ['pageFilters' => $this->filters], key('seller-contacts-ranking-' . md5(json_encode($this->filters))))

            @livewire(\App\Filament\Widgets\Reports\ContactsTableWidget::class, ['pageFilters' => $this->filters], key('contacts-table-' . md5(json_encode($this->filters))))
        </div>
    @endif
</x-filament-panels::page>
