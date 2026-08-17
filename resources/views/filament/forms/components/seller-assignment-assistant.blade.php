<div
    class="p-2 space-y-4"
>
    @php
        $clientId = $clientId ?? null;
        $client = $clientId ? \App\Models\Client::find($clientId) : null;
        $workload = \App\Services\SellerWorkloadService::getSellersWorkload($clientId);
    @endphp

    @if ($client)
        <div class="bg-primary-50 dark:bg-primary-950/40 border border-primary-200 dark:border-primary-800/50 rounded-xl p-3 flex items-center gap-3">
            <x-filament::icon icon="heroicon-o-user-circle" class="h-5 w-5 text-primary-600 dark:text-primary-400 shrink-0" />
            <div class="text-xs text-primary-900 dark:text-primary-200">
                <span>Analisando carga de trabalho para o cliente: <strong>{{ $client->name }}</strong> ({{ $client->email ?? 'Sem e-mail' }})</span>
            </div>
        </div>
    @endif

    <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50 rounded-xl p-4 flex items-start gap-3">
        <x-filament::icon icon="heroicon-o-light-bulb" class="h-6 w-6 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
        <div class="text-xs text-amber-900 dark:text-amber-200 space-y-1">
            <p class="font-bold text-sm">💡 Como escolher o melhor vendedor?</p>
            <p>A lista abaixo prioriza vendedores que <strong>já fecharam negócios com este cliente</strong>, possuem <strong>menor carga ativa</strong> e estão <strong>há mais tempo sem atribuição</strong>.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($workload as $index => $seller)
            @php
                $isRecommended = $index === 0;
                $hasClientHistory = $seller['closed_deals_with_client_count'] > 0;
            @endphp
            <div class="relative border rounded-xl p-4 transition-all duration-200 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-primary-500 shadow-sm flex flex-col justify-between {{ $isRecommended ? 'ring-2 ring-primary-500/50 dark:ring-primary-400/50 bg-primary-50/20 dark:bg-primary-950/20' : '' }}">
                
                @if ($isRecommended)
                    <span class="absolute -top-3 right-4 bg-primary-600 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full shadow-sm flex items-center gap-1">
                        {{-- @if ($hasClientHistory)
                            🤝 Já vendeu para este cliente
                        @else --}}
                            ⭐ Recomendado para Atribuição
                        {{-- @endif --}}
                    </span>
                @endif

                <div>
                    {{-- Cabeçalho do Vendedor --}}
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-11 h-11 rounded-full bg-slate-100 dark:bg-slate-800 ring-2 ring-slate-200 dark:ring-slate-700 flex items-center justify-center font-extrabold text-sm text-slate-700 dark:text-slate-200 overflow-hidden shrink-0">
                            @if (!empty($seller['avatar']))
                                <img src="{{ $seller['avatar'] }}" alt="{{ $seller['name'] }}" class="w-full h-full object-cover" />
                            @else
                                <span>{{ $seller['initials'] }}</span>
                            @endif
                        </div>
                        <div class="truncate">
                            <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100 truncate">{{ $seller['name'] }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $seller['email'] }}</p>
                        </div>
                    </div>

                    {{-- Histórico com Este Cliente --}}
                    @if ($hasClientHistory)
                        <div class="mb-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 p-2.5 rounded-lg text-xs flex items-center justify-between text-emerald-900 dark:text-emerald-200">
                            <div class="flex items-center gap-1.5 font-bold">
                                <x-filament::icon icon="heroicon-o-check-badge" class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                                <span>{{ $seller['closed_deals_with_client_count'] }} {{ $seller['closed_deals_with_client_count'] === 1 ? 'venda realizada' : 'vendas realizadas' }} a este cliente</span>
                            </div>
                            <span class="font-extrabold text-emerald-700 dark:text-emerald-300 text-[11px]">
                                R$ {{ number_format($seller['closed_deals_with_client_value'], 2, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    {{-- Indicadores Globais --}}
                    <div class="grid grid-cols-2 gap-2 mb-3 bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-lg text-xs">
                        <div>
                            <span class="text-[10px] font-semibold uppercase text-slate-400">Em Aberto / Carga</span>
                            <p class="font-bold text-slate-900 dark:text-slate-100">{{ $seller['active_deals_count'] }} negócios</p>
                            <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">R$ {{ number_format($seller['active_deals_value'], 2, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-semibold uppercase text-slate-400">Vendas do Mês</span>
                            <p class="font-bold text-emerald-600 dark:text-emerald-400">{{ $seller['won_deals_count'] }} concluídas</p>
                            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300">R$ {{ number_format($seller['won_deals_value'], 2, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Recência --}}
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mb-4">
                        <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4 text-slate-400 shrink-0" />
                        <span>Último negócio atribuído: <strong class="text-slate-700 dark:text-slate-300">{{ $seller['last_assigned_human'] }}</strong></span>
                    </div>
                </div>

                {{-- Botão Seleção --}}
                <button
                    type="button"
                    x-on:click="$wire.callMountedAction({ sellerId: {{ $seller['id'] }} })"
                    class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold rounded-lg text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 transition-colors shadow-sm"
                >
                    <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                    <span>Atribuir a {{ strtok($seller['name'], ' ') }}</span>
                </button>
                
            </div>
        @endforeach
    </div>
</div>
