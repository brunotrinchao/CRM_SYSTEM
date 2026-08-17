<div
    x-data="{
        draggedDealId: null,
        draggedOverColumn: null,
    }"
    class="space-y-4"
>
    {{-- Barra de Busca e Filtros Rápidos do Kanban --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 p-3.5 rounded-2xl shadow-sm">
        <div class="relative flex-1 max-w-lg">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="absolute left-3.5 top-3 h-4 w-4 text-slate-400" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por título, cliente ou vendedor no Kanban..."
                class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all shadow-inner"
            />
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 font-medium shrink-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/60 dark:border-slate-700/60">
                <x-filament::icon icon="heroicon-o-cursor-arrow-rays" class="h-4 w-4 text-primary-500" />
                Clique no card para abrir os detalhes
            </span>
            <span class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/60 dark:border-slate-700/60">
                <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-4 w-4 text-amber-500" />
                Arraste entre colunas para alterar o status
            </span>
        </div>
    </div>

    {{-- Board Kanban --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-start">
        @foreach (\App\Enums\DealStatus::cases() as $status)
            @php
                $columnDeals = $deals->get($status->value, collect());
                $columnTotalCount = $columnDeals->count();
                $columnTotalValue = $columnDeals->sum('total_value');

                $accentBorder = match ($status->value) {
                    'PENDING' => 'border-t-4 border-t-slate-400 dark:border-t-slate-500',
                    'NEGOTIATING' => 'border-t-4 border-t-amber-500',
                    'WON' => 'border-t-4 border-t-emerald-500',
                    'LOST' => 'border-t-4 border-t-rose-500',
                    'CANCELLED' => 'border-t-4 border-t-slate-700 dark:border-t-slate-600',
                    default => 'border-t-4 border-t-primary-500',
                };

                $dotBg = match ($status->value) {
                    'PENDING' => 'bg-slate-400',
                    'NEGOTIATING' => 'bg-amber-500',
                    'WON' => 'bg-emerald-500',
                    'LOST' => 'bg-rose-500',
                    'CANCELLED' => 'bg-slate-600',
                    default => 'bg-primary-500',
                };
            @endphp

            <div
                x-on:dragover.prevent="draggedOverColumn = '{{ $status->value }}'"
                x-on:dragleave="if (draggedOverColumn === '{{ $status->value }}') draggedOverColumn = null"
                x-on:drop.prevent="
                    if (draggedDealId) {
                        $wire.moveDeal(draggedDealId, '{{ $status->value }}');
                    }
                    draggedOverColumn = null;
                "
                :class="{ 'ring-2 ring-primary-500/80 bg-primary-50/40 dark:bg-primary-950/40 scale-[1.01]': draggedOverColumn === '{{ $status->value }}' }"
                class="flex flex-col bg-slate-100/70 dark:bg-slate-900/50 backdrop-blur-sm border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-3.5 min-h-[580px] transition-all duration-200 {{ $accentBorder }} shadow-sm"
            >
                {{-- Cabeçalho da Coluna --}}
                <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-200/80 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $dotBg }} shadow-sm"></span>
                        <h3 class="font-extrabold text-xs uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            {{ $status->label() }}
                        </h3>
                    </div>
                    <span class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs px-2.5 py-0.5 rounded-full font-black border border-slate-200 dark:border-slate-700 shadow-2xs">
                        {{ $columnTotalCount }}
                    </span>
                </div>

                {{-- Soma Financeira da Coluna --}}
                <div class="bg-white/80 dark:bg-slate-800/50 p-2.5 rounded-xl border border-slate-200/70 dark:border-slate-700/50 mb-3 flex items-center justify-between shadow-2xs">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Acumulado</span>
                    <span class="font-extrabold text-slate-900 dark:text-slate-100 font-finance text-xs tracking-tight">
                        R$ {{ number_format($columnTotalValue, 2, ',', '.') }}
                    </span>
                </div>

                {{-- Container de Cards --}}
                <div class="space-y-3 flex-1 overflow-y-auto pr-0.5">
                    @forelse ($columnDeals as $deal)
                        @php
                            $latestDiscount = $deal->discountRequests->sortByDesc('created_at')->first();
                            $leftBorder = match ($latestDiscount?->status?->value) {
                                'PENDING' => 'border-l-4 border-l-amber-500',
                                'APPROVED' => 'border-l-4 border-l-emerald-500',
                                'REJECTED' => 'border-l-4 border-l-rose-500',
                                default => 'border-l-2 border-l-slate-200 dark:border-l-slate-800',
                            };
                            $isDraggable = !in_array($deal->status->value, ['CANCELLED', 'WON', 'LOST']);
                            $sellerInitials = strtoupper(substr($deal->user?->name ?? 'U', 0, 2));
                        @endphp

                        <div
                            draggable="{{ $isDraggable ? 'true' : 'false' }}"
                            x-on:dragstart="draggedDealId = {{ $deal->id }}; $el.classList.add('opacity-40')"
                            x-on:dragend="draggedDealId = null; $el.classList.remove('opacity-40')"
                            @click="Livewire.find('{{ $parentId }}').call('openDealView', {{ $deal->id }})"
                            class="group relative bg-white dark:bg-slate-900 border rounded-2xl p-4 shadow-sm space-y-3 transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 border-slate-200/90 dark:border-slate-800 hover:border-primary-500/50 dark:hover:border-primary-400/50 cursor-pointer {{ $leftBorder }} {{ $isDraggable ? 'active:cursor-grabbing' : '' }}"
                        >
                            {{-- Top Header Card: Title & Lock --}}
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-extrabold text-xs text-slate-900 dark:text-slate-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors line-clamp-2 leading-snug">
                                    {{ $deal->title }}
                                </h4>
                                <div class="flex items-center gap-1 shrink-0">
                                    @if(!$isDraggable)
                                        <x-filament::icon icon="heroicon-o-lock-closed" class="h-3.5 w-3.5 text-slate-400" title="Negócio finalizado ou cancelado" />
                                    @else
                                        <x-filament::icon icon="heroicon-o-bars-2" class="h-3.5 w-3.5 text-slate-300 dark:text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity" title="Arraste para mover" />
                                    @endif
                                </div>
                            </div>

                            {{-- Client Pill --}}
                            <div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/60 p-2 rounded-xl border border-slate-100 dark:border-slate-800">
                                <x-filament::icon icon="heroicon-o-user" class="h-3.5 w-3.5 text-primary-500 shrink-0" />
                                <span class="truncate font-semibold text-slate-800 dark:text-slate-200">{{ $deal->client?->name ?? 'Sem cliente' }}</span>
                            </div>

                            {{-- Indicator Badge for Discount Request --}}
                            @if ($latestDiscount)
                                <div class="text-[10px] font-extrabold px-2.5 py-1 rounded-lg inline-flex items-center gap-1.5 shadow-2xs {{ match ($latestDiscount->status?->value) { 'PENDING' => 'bg-amber-50 text-amber-900 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800', 'APPROVED' => 'bg-emerald-50 text-emerald-900 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800', 'REJECTED' => 'bg-rose-50 text-rose-900 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800', default => 'bg-slate-100 text-slate-700' } }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ match ($latestDiscount->status?->value) { 'PENDING' => 'bg-amber-500 animate-pulse', 'APPROVED' => 'bg-emerald-500', 'REJECTED' => 'bg-rose-500', default => 'bg-slate-400' } }}"></span>
                                    <span>Desconto {{ match ($latestDiscount->status?->value) { 'PENDING' => 'Pendente', 'APPROVED' => 'Aprovado', 'REJECTED' => 'Rejeitado', default => '' } }}</span>
                                </div>
                            @endif

                            {{-- Footer: Seller Avatar & Total Value --}}
                            <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 ring-2 ring-slate-300/50 dark:ring-slate-700/50 flex items-center justify-center font-extrabold text-[10px] text-slate-700 dark:text-slate-300 overflow-hidden shrink-0">
                                        @if (!empty($deal->user?->avatar))
                                            <img src="{{ $deal->user->avatar }}" alt="{{ $deal->user->name }}" class="w-full h-full object-cover" />
                                        @else
                                            <span>{{ $sellerInitials }}</span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 truncate max-w-[85px]">
                                        {{ strtok($deal->user?->name ?? 'Sem dono', ' ') }}
                                    </span>
                                </div>

                                <div class="text-right">
                                    <span class="font-extrabold text-slate-900 dark:text-slate-100 font-finance text-xs tracking-tight">
                                        R$ {{ number_format($deal->total_value, 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="h-32 border-2 border-dashed border-slate-200 dark:border-slate-800/80 rounded-2xl flex flex-col items-center justify-center p-4 text-center text-slate-400 text-xs gap-1">
                            <x-filament::icon icon="heroicon-o-inbox" class="h-5 w-5 text-slate-300 dark:text-slate-700" />
                            <span>Nenhum negócio</span>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal de Confirmação para CANCELADO --}}
    @if ($showCancelModal && $pendingCancelDeal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-md animate-fade-in">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5">
                <div class="flex items-center gap-3.5">
                    <div class="p-3 bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 rounded-2xl shrink-0">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-7 w-7" />
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900 dark:text-slate-100">
                            Confirmar Cancelamento
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                            Ação definitiva e irreversível
                        </p>
                    </div>
                </div>

                <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 p-4 rounded-2xl space-y-2">
                    <p class="text-xs text-rose-950 dark:text-rose-200">
                        Tem certeza de que deseja cancelar o negócio <strong>"{{ $pendingCancelDeal->title }}"</strong>?
                    </p>
                    <p class="text-[11px] text-rose-700 dark:text-rose-300 font-bold flex items-center gap-1">
                        ⚠️ O cancelamento não poderá ser desfeito e o botão de edição será bloqueado.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="closeCancelModal"
                        class="px-4 py-2.5 text-xs font-extrabold rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                    >
                        Voltar
                    </button>
                    <button
                        type="button"
                        wire:click="executeCancelDeal"
                        class="px-4 py-2.5 text-xs font-extrabold rounded-xl bg-rose-600 hover:bg-rose-500 text-white shadow-md transition-all"
                    >
                        Sim, Cancelar Negócio
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Confirmação para PERDIDO --}}
    @if ($showLostModal && $pendingLostDeal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-md animate-fade-in">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5">
                <div class="flex items-center gap-3.5">
                    <div class="p-3 bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 rounded-2xl shrink-0">
                        <x-filament::icon icon="heroicon-o-hand-thumb-down" class="h-7 w-7" />
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900 dark:text-slate-100">
                            Confirmar Status Perdido
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                            Alteração de Status do Negócio
                        </p>
                    </div>
                </div>

                <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 p-4 rounded-2xl space-y-2">
                    <p class="text-xs text-amber-950 dark:text-amber-200">
                        Tem certeza de que deseja alterar o status do negócio <strong>"{{ $pendingLostDeal->title }}"</strong> para <strong class="text-amber-600 dark:text-amber-400">Perdido</strong>?
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="closeLostModal"
                        class="px-4 py-2.5 text-xs font-extrabold rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
                    >
                        Voltar
                    </button>
                    <button
                        type="button"
                        wire:click="executeLostDeal"
                        class="px-4 py-2.5 text-xs font-extrabold rounded-xl bg-amber-600 hover:bg-amber-500 text-white shadow-md transition-all"
                    >
                        Confirmar como Perdido
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
