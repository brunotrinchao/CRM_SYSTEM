<x-filament-panels::page>
    <div x-data="{ activeView: $wire.entangle('activeView') }" class="space-y-4">
        {{-- Switcher de Visão: Tabela x Kanban --}}
        <div class="flex items-center justify-between gap-4 py-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Modo de Exibição:</span>
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-bold">
                    <button
                        type="button"
                        @click="activeView = 'table'"
                        :class="activeView === 'table' ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                        class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5"
                    >
                        <x-filament::icon icon="heroicon-o-table-cells" class="h-4 w-4" />
                        Listagem (Tabela)
                    </button>
                    <button
                        type="button"
                        @click="activeView = 'kanban'"
                        :class="activeView === 'kanban' ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                        class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5"
                    >
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="h-4 w-4" />
                        Kanban
                    </button>
                </div>
            </div>
        </div>

        {{-- Visão Kanban --}}
        <div x-show="activeView === 'kanban'" x-cloak>
            <livewire:deals-kanban :table-filters="$tableFilters" :table-search="$tableSearch" :parent-id="$this->getId()" />
        </div>

        {{-- Visão Tabela --}}
        <div x-show="activeView === 'table'">
            {{ $this->table }}
        </div>
    </div>

    @if (count($getFooterWidgets = $this->getFooterWidgets()))
        <x-filament-widgets::widgets
            :widgets="$getFooterWidgets"
            :data="$this->getFooterWidgetsData()"
        />
    @endif
</x-filament-panels::page>
