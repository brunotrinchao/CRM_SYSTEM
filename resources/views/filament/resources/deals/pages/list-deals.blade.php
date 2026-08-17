<x-filament-panels::page>

    <x-filament::tabs label="Content tabs">

        {{-- Aba 1: Listagem --}}
        <x-filament::tabs.item :active="$activeView === 'listagem'" wire:click="$set('activeTab', 'listagem')"
            icon="heroicon-o-table-cells">
            Listagem
        </x-filament::tabs.item>

        {{-- Aba 2: Kanban --}}
        <x-filament::tabs.item :active="$activeView === 'kanban'" wire:click="$set('activeTab', 'kanban')"
            icon="heroicon-o-squares-2x2">
            Kanban
        </x-filament::tabs.item>

    </x-filament::tabs>


    {{-- Painel único de filtros: afeta Listagem e Kanban ao mesmo tempo (escreve direto
    em tableFilters, o mesmo estado que os dois já consomem). O botão nativo de
    filtros da Tabela foi escondido (DealsTable::configure) pra não duplicar UI. --}}
    @php
        $vendedorOptions = \App\Models\User::where('profile', \App\Enums\UserProfile::USER)->pluck('name', 'id');
        $criadoPorOptions = \App\Models\User::whereIn('profile', [\App\Enums\UserProfile::ADMIN, \App\Enums\UserProfile::MANAGER])->pluck('name', 'id');
        $statusOptions = \App\Enums\DealStatus::options();
        $activeFiltersCount = collect([
            $tableFilters['user_id']['value'] ?? null,
            $tableFilters['created_by']['value'] ?? null,
            ($tableFilters['has_pending_discount']['pending'] ?? false) ? true : null,
            filled($tableFilters['status']['values'] ?? null) ? true : null,
            $tableFilters['trashed']['value'] ?? null,
            $filterDateFrom,
            $filterDateUntil,
        ])->filter()->count();
    @endphp
    <div x-data="{ open: false }" class="relative flex justify-end">
        <x-filament::button size="lg" @click="$dispatch('open-modal', { id: 'open-filter' })" color="mute">
            <x-filament::icon icon="heroicon-o-funnel" class="h-4 w-4" />
            Filtros
            @if ($activeFiltersCount > 0)
                <span
                    class="inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-primary-600 text-white text-[10px] font-black">{{ $activeFiltersCount }}</span>
            @endif
        </x-filament::button>


        {{-- <div x-show="open" x-cloak @click.outside="open = false"
            class="absolute right-0 top-full mt-2 z-20 w-[22rem] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl p-4 space-y-4">

        </div> --}}

        <x-filament::modal id="open-filter" slide-over sticky-header sticky-footer>
            <x-slot name="heading">
                Filtros
            </x-slot>

            <div class="space-y-5">

                {{-- Vendedor Responsável --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400">Vendedor Responsável</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="tableFilters.user_id.value">
                            <option value="">Todos</option>
                            @foreach ($vendedorOptions as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                {{-- Criado Por --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400">Criado Por</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="tableFilters.created_by.value">
                            <option value="">Todos</option>
                            @foreach ($criadoPorOptions as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                {{-- Status (Múltipla Seleção) --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block">Status</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="tableFilters.status.values" multiple
                            class="py-1">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                    <p class="text-[11px] text-slate-400">Segure Ctrl/Cmd para selecionar múltiplos itens.</p>
                </div>

                {{-- Data de Ganho (De / Até) --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block">Data de Ganho</label>
                    <div class="grid grid-cols-1 sm:grid-cols-[1fr,auto,1fr] items-center gap-2">
                        <x-filament::input.wrapper>
                            <x-filament::input type="date" wire:model.live="filterDateFrom" />
                        </x-filament::input.wrapper>
                        <span class="text-xs text-slate-400 text-center">até</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="date" wire:model.live="filterDateUntil" />
                        </x-filament::input.wrapper>
                    </div>
                </div>

                {{-- Registros Excluídos --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400">Registros Excluídos</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="tableFilters.trashed.value">
                            <option value="">Sem excluídos</option>
                            <option value="with">Com excluídos</option>
                            <option value="only">Só excluídos</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                {{-- Checkbox: Desconto Pendente --}}
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <x-filament::input.checkbox wire:model.live="tableFilters.has_pending_discount.pending" />
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">
                            Apenas com desconto pendente
                        </span>
                    </label>
                </div>

            </div>

            <x-slot name="footer">
                <x-filament::button color="gray" wire:click="clearAllFilters">
                    Limpar filtros
                </x-filament::button>
            </x-slot>
        </x-filament::modal>
    </div>

    <div x-data="{ activeView: $wire.entangle('activeView') }" class="space-y-4">
        {{-- Switcher de Visão: Tabela x Kanban --}}
        {{-- <div class="flex items-center justify-between gap-4 py-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Modo de Exibição:</span>
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-bold">
                    <button type="button" @click="activeView = 'table'"
                        :class="activeView === 'table' ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                        class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5">
                        <x-filament::icon icon="heroicon-o-table-cells" class="h-4 w-4" />
                        Listagem
                    </button>
                    <button type="button" @click="activeView = 'kanban'"
                        :class="activeView === 'kanban' ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                        class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5">
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="h-4 w-4" />
                        Kanban
                    </button>
                </div>
            </div>
        </div> --}}

        {{-- Visão Kanban --}}
        @if ($activeTab === 'listagem')
            {{ $this->table }}

            {{-- Visão Tabela --}}
        @else
            <livewire:deals-kanban :table-filters="$tableFilters" :table-search="$tableSearch" />
        @endif
    </div>

    @if (count($getFooterWidgets = $this->getFooterWidgets()))
        <x-filament-widgets::widgets :widgets="$getFooterWidgets" :data="$this->getFooterWidgetsData()" />
    @endif
</x-filament-panels::page>