<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportHeaderAction;
use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportBulkAction;

class ProductsStockTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Valoração e Saldo de Estoque por Produto';
    protected ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['category'])
                    ->selectRaw('products.*, (current_stock * price) as calculated_valuation')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome do Produto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço Unitário')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Estoque Atual')
                    ->sortable(),

                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Estoque Mínimo')
                    ->sortable(),

                Tables\Columns\TextColumn::make('calculated_valuation')
                    ->label('Valoração Total')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Status do Estoque')
                    ->badge()
                    ->state(function (Product $record): string {
                        return $record->current_stock <= $record->minimum_stock ? 'Estoque Crítico' : 'Estoque Normal';
                    })
                    ->color(function (Product $record): string {
                        return $record->current_stock <= $record->minimum_stock ? 'danger' : 'success';
                    }),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export_products')
                    ->label('Exportar Estoque')
                    ->fileName('relatorio_estoque_' . now()->format('Y-m-d')),
            ])
            ->bulkActions([
                FilamentExportBulkAction::make('export_selected_products')
                    ->label('Exportar Selecionados'),
            ])
            ->defaultSort('name', 'asc')
            ->paginated([10, 25, 50]);
    }
}
