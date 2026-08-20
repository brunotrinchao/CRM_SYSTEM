<x-filament::widget>
    <x-filament::section>
        {{-- Cabeçalho do Widget com Título e Ícone --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <x-filament::icon
                    icon="heroicon-o-funnel"
                    class="w-5 h-5 text-primary-500"
                />
                <span class="text-xs font-bold tracking-wider uppercase text-slate-700 dark:text-slate-200">
                    Funil de Vendas
                </span>
            </div>
        </div>

        {{-- Estágios em colunas no desktop (5 colunas para os 5 estágios), adaptável no mobile --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mt-4">
            @foreach ($stages as $stage)
                <div class="p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl flex flex-col justify-between shadow-sm space-y-3">
                    
                    {{-- Topo do Card: Título e Indicador Colorido --}}
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                            {{ $stage['title'] }}
                        </span>
                        
                        {{-- Cor do Indicador --}}
                        @php
                            $hexColor = match($stage['color']) {
                                'warning' => '#f59e0b',
                                'primary' => '#2563eb',
                                'success' => '#10b981',
                                'danger'  => '#f43f5e',
                                default   => '#64748b',
                            };
                        @endphp
                        <span class="w-2.5 h-2.5 rounded-full inline-block shrink-0" style="background-color: {{ $hexColor }};"></span>
                    </div>

                    {{-- Corpo do Card: Quantidade e Valor Financeiro --}}
                    <div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-extrabold text-slate-900 dark:text-slate-100">
                                {{ $stage['count'] }}
                            </span>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                {{ $stage['count'] === 1 ? 'negócio' : 'negócios' }}
                            </span>
                        </div>
                        
                        <div class="mt-1 text-xs sm:text-sm font-bold text-emerald-600 dark:text-emerald-400 font-finance">
                            R$ {{ number_format($stage['value'], 2, ',', '.') }}
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament::widget>