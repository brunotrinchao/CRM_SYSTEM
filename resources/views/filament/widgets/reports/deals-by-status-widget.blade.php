<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Negócios por Status{{ $this->getPeriodTitleSuffix() }}
        </x-slot>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach ($this->getStatusSummary() as $item)
                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ $item['label'] }}
                        </span>
                        <x-filament::badge :color="$item['color']">
                            {{ $item['count'] }}
                        </x-filament::badge>
                    </div>

                    <div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ \Illuminate\Support\Number::currency($item['total_value'], 'BRL') }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $item['percentage'] }}% do total
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
