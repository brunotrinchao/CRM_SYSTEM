<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class StockOverviewStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $products = Product::query()->get();

        // 1. Valoração Total do Estoque (current_stock * price)
        $totalValuation = $products->sum(function ($product) {
            return (float) ($product->current_stock * $product->price);
        });

        // 2. Total de Produtos
        $totalProducts = $products->count();

        // 3. Itens com Alerta Crítico (current_stock <= minimum_stock)
        $criticalItemsCount = $products->filter(function ($product) {
            return $product->current_stock <= $product->minimum_stock;
        })->count();

        $criticalColor = $criticalItemsCount > 0 ? 'danger' : 'success';

        return [
            Stat::make('Valoração Total do Estoque', Number::currency($totalValuation, 'BRL'))
                ->description('Soma financeira do inventário')
                ->descriptionIcon(Phosphor::PackageDuotone)
                ->color('primary'),

            Stat::make('Total de Produtos', number_format($totalProducts, 0, ',', '.'))
                ->description('Itens cadastrados no sistema')
                ->descriptionIcon(Phosphor::CubeDuotone)
                ->color('info'),

            Stat::make('Itens com Alerta Crítico', number_format($criticalItemsCount, 0, ',', '.'))
                ->description($criticalItemsCount > 0 ? 'Abaixo do estoque mínimo' : 'Estoque regularizado')
                ->descriptionIcon(Phosphor::WarningDuotone)
                ->color($criticalColor),
        ];
    }
}
