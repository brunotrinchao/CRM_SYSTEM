<x-filament::section>
    <x-slot name="heading">
        <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400 font-bold">
            <x-filament::icon icon="heroicon-o-trophy" class="h-5 w-5 text-amber-500" />
            <span>Ranking de Desempenho</span>
        </div>
    </x-slot>

    @php
        $ranking = $this->getRankingData();
        $top3 = $ranking['top3'];
        $others = $ranking['others'];

        $first = $top3[0] ?? null;
        $second = $top3[1] ?? null;
        $third = $top3[2] ?? null;
    @endphp

    @if ($first || $second || $third)
        {{-- Pódio dos 3 Primeiros Vendedores --}}
        <div class="grid grid-cols-3 gap-3 mb-6 items-end pt-4 pb-2">
            {{-- 2º Lugar --}}
            <div class="flex flex-col items-center">
                @if ($second)
                    <div class="relative mb-2">
                        <div class="w-14 h-14 rounded-full ring-4 ring-slate-300 dark:ring-slate-500 overflow-hidden flex items-center justify-center bg-slate-200 dark:bg-slate-700 font-bold text-slate-700 dark:text-slate-200 shadow-md">
                            @if (!empty($second['avatar']))
                                <img src="{{ $second['avatar'] }}" alt="{{ $second['name'] }}" class="w-full h-full object-cover" />
                            @else
                                <span class="text-base font-bold">{{ $second['initials'] }}</span>
                            @endif
                        </div>
                        <span class="absolute -top-2 -right-1 bg-slate-400 text-white text-[11px] font-extrabold px-1.5 py-0.5 rounded-full shadow">2º</span>
                    </div>
                    <p class="font-semibold text-xs text-center truncate w-full dark:text-slate-200" title="{{ $second['name'] }}">{{ $second['name'] }}</p>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ $second['deals_count'] }} vendas</span>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">R$ {{ number_format($second['total_value'], 2, ',', '.') }}</span>
                @endif
            </div>

            {{-- 1º Lugar (Destaque Principal) --}}
            <div class="flex flex-col items-center -mt-3">
                @if ($first)
                    <div class="relative mb-2">
                        <div class="w-16 h-16 rounded-full ring-4 ring-amber-400 dark:ring-amber-500 overflow-hidden flex items-center justify-center bg-gradient-to-br from-amber-400 to-yellow-600 text-white font-black shadow-lg shadow-amber-500/30">
                            @if (!empty($first['avatar']))
                                <img src="{{ $first['avatar'] }}" alt="{{ $first['name'] }}" class="w-full h-full object-cover" />
                            @else
                                <span class="text-lg font-black">{{ $first['initials'] }}</span>
                            @endif
                        </div>
                        <span class="absolute -top-2.5 -right-1 bg-amber-500 text-white text-[11px] font-black px-2 py-0.5 rounded-full shadow-md">🥇 1º</span>
                    </div>
                    <p class="font-bold text-xs text-center truncate w-full text-amber-600 dark:text-amber-400" title="{{ $first['name'] }}">{{ $first['name'] }}</p>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold">{{ $first['deals_count'] }} vendas</span>
                    <span class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($first['total_value'], 2, ',', '.') }}</span>
                @endif
            </div>

            {{-- 3º Lugar --}}
            <div class="flex flex-col items-center">
                @if ($third)
                    <div class="relative mb-2">
                        <div class="w-14 h-14 rounded-full ring-4 ring-amber-700/50 dark:ring-amber-700/70 overflow-hidden flex items-center justify-center bg-amber-100 dark:bg-amber-950/60 font-bold text-amber-800 dark:text-amber-200 shadow-md">
                            @if (!empty($third['avatar']))
                                <img src="{{ $third['avatar'] }}" alt="{{ $third['name'] }}" class="w-full h-full object-cover" />
                            @else
                                <span class="text-base font-bold">{{ $third['initials'] }}</span>
                            @endif
                        </div>
                        <span class="absolute -top-2 -right-1 bg-amber-700 text-white text-[11px] font-extrabold px-1.5 py-0.5 rounded-full shadow">3º</span>
                    </div>
                    <p class="font-semibold text-xs text-center truncate w-full dark:text-slate-200" title="{{ $third['name'] }}">{{ $third['name'] }}</p>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ $third['deals_count'] }} vendas</span>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">R$ {{ number_format($third['total_value'], 2, ',', '.') }}</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Lista do Restante dos Vendedores (4º em diante) --}}
    @if (count($others) > 0)
        <div class="border-t border-slate-200 dark:border-slate-700/60 pt-4 space-y-2">
            <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Outras Posições</h4>
            @foreach ($others as $seller)
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <div class="flex items-center gap-2.5">
                        <span class="w-5 text-center text-xs font-extrabold text-slate-400">{{ $seller['rank'] }}º</span>
                        
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300 overflow-hidden">
                            @if (!empty($seller['avatar']))
                                <img src="{{ $seller['avatar'] }}" alt="{{ $seller['name'] }}" class="w-full h-full object-cover" />
                            @else
                                <span>{{ $seller['initials'] }}</span>
                            @endif
                        </div>
                        
                        <div class="truncate">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $seller['name'] }}</p>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ $seller['deals_count'] }} vendas</span>
                        </div>
                    </div>

                    <div class="text-right whitespace-nowrap">
                        <span class="text-xs font-bold text-slate-900 dark:text-slate-100">
                            R$ {{ number_format($seller['total_value'], 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if (empty($first) && empty($second) && empty($third))
        <div class="py-8 text-center text-slate-500 dark:text-slate-400 text-xs">
            Nenhum vendedor encontrado com vendas no período selecionado.
        </div>
    @endif
</x-filament::section>
