@php
    $pendingDeals = $this->getPendingDeals();
    $totalCount = $pendingDeals->count();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-phone-arrow-up-right" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    <span class="font-extrabold text-sm text-slate-900 dark:text-slate-100">Contatos Pendentes</span>
                    <span class="bg-amber-500 text-white font-extrabold px-2 py-0.5 rounded-full text-xs shrink-0 shadow-sm">
                        {{ $totalCount }}
                    </span>
                </div>
            </div>
        </x-slot>

        @if ($pendingDeals->isEmpty())
            <div class="py-8 text-center space-y-2">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-8 w-8 text-emerald-500 mx-auto" />
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Todos os contatos estão em dia!</p>
            </div>
        @else
            <div class="space-y-3 max-h-[550px] overflow-y-auto pr-1">
                @foreach ($pendingDeals as $item)
                    @php
                        $deal = $item['deal'];
                        $isRed = $item['is_overdue_24h'];
                    @endphp
                    <div
                        wire:click="mountAction('viewDealAction', { record: {{ $deal->id }} })"
                        class="relative p-3 rounded-xl border transition-all duration-200 shadow-sm flex flex-col justify-between space-y-2 cursor-pointer hover:shadow-md hover:scale-[1.01] {{ $isRed ? 'bg-red-50/80 dark:bg-red-950/40 border-red-300 dark:border-red-800/60 ring-1 ring-red-400/40' : 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800/40 hover:bg-amber-100/60 dark:hover:bg-amber-900/30' }}"
                    >
                        {{-- Header do Card --}}
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-bold text-xs hover:underline truncate text-slate-900 dark:text-slate-100">
                                {{ $deal->title }}
                            </span>
                            @if ($isRed)
                                <span class="bg-red-600 text-white font-extrabold px-2 py-0.5 rounded-full text-[10px] shrink-0">
                                    Contato em atraso ({{ $item['hours_diff'] }}h)
                                </span>
                            @else
                                <span class="bg-amber-500 text-white font-bold px-2 py-0.5 rounded-full text-[10px] shrink-0">
                                    Pendente
                                </span>
                            @endif
                        </div>

                        {{-- Detalhes --}}
                        <div class="text-[11px] text-slate-600 dark:text-slate-300 space-y-1">
                            <div class="flex items-center gap-1.5 truncate">
                                <x-filament::icon icon="heroicon-o-user" class="h-3.5 w-3.5 text-slate-400 shrink-0" />
                                <span class="truncate">Cliente: <strong>{{ $item['client_name'] }}</strong></span>
                            </div>
                            @if (Auth::user()?->profile !== \App\Enums\UserProfile::USER)
                                <div class="flex items-center gap-1.5 truncate">
                                    <x-filament::icon icon="heroicon-o-user-circle" class="h-3.5 w-3.5 text-slate-400 shrink-0" />
                                    <span class="truncate">Vendedor: <strong>{{ $item['seller_name'] }}</strong></span>
                                </div>
                            @endif
                            <div class="flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-o-calendar" class="h-3.5 w-3.5 text-slate-400 shrink-0" />
                                <span>Previsão: <strong>{{ $item['next_contact_human'] }}</strong></span>
                            </div>
                        </div>

                        {{-- Botão de Ação Rápida (Apenas para Vendedores USER) --}}
                        @if (Auth::user()?->profile === \App\Enums\UserProfile::USER)
                            <div class="pt-1" onclick="event.stopPropagation()">
                                <button
                                    type="button"
                                    wire:click.stop="mountAction('addNoteAction', { deal_id: {{ $deal->id }} })"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 text-xs font-bold rounded-lg text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 transition-colors shadow-sm"
                                >
                                    <x-filament::icon icon="heroicon-o-plus-circle" class="h-3.5 w-3.5" />
                                    <span>Registrar Contato</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <x-filament-actions::modals />
    </x-filament::section>
</x-filament-widgets::widget>
