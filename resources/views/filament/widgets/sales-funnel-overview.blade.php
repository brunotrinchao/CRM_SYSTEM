<x-filament::widget>
    <x-filament::section>
        {{-- Cabeçalho do Widget com Título e Link --}}
        <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 1rem; border-bottom: 1px solid rgba(107, 114, 128, 0.2);">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <x-filament::icon
                    icon="heroicon-o-funnel"
                    class="w-5 h-5 text-primary-500"
                />
                <span style="font-size: 0.875rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;">
                    Funil de Vendas
                </span>
            </div>
        </div>

        {{-- Grade de Estágios usando Flexbox com quebra automática --}}
        <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
            @foreach ($stages as $stage)
                <div style="flex: 1; min-width: 180px; padding: 1rem; background-color: var(--fi-widget-bg, #ffffff); border: 1px solid rgba(107, 114, 128, 0.2); border-radius: 0.75rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                    
                    {{-- Topo do Card: Título e Indicador Colorido --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.875rem; font-weight: 600;">
                            {{ $stage['title'] }}
                        </span>
                        
                        {{-- Cor do Indicador --}}
                        @php
                            $hexColor = match($stage['color']) {
                                'warning' => '#f59e0b',
                                'primary' => '#2563eb',
                                'success' => '#10b981',
                                'danger'  => '#f43f5e',
                                default   => '#475569',
                            };
                        @endphp
                        <span style="width: 10px; height: 10px; border-radius: 9999px; background-color: {{ $hexColor }}; display: inline-block;"></span>
                    </div>

                    {{-- Corpo do Card: Quantidade e Valor Financeiro --}}
                    <div>
                        <div style="display: flex; align-items: baseline; gap: 0.375rem;">
                            <span style="font-size: 1.25rem; font-weight: 700;">
                                {{ $stage['count'] }}
                            </span>
                            <span style="font-size: 0.75rem; color: #6b7280;">
                                negócios
                            </span>
                        </div>
                        
                        <div style="margin-top: 0.25rem; font-size: 0.875rem; font-weight: 700; color: #059669; font-family: 'Space Mono', monospace;">
                            R$ {{ number_format($stage['value'], 2, ',', '.') }}
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament::widget>