<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Schemas\ProductInfolist;
use App\Models\Product;
use App\Services\ProductService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->badge()
                    ->color(Color::Neutral)
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')

            ->extraAttributes(fn (): array => ['class' => 'font-finance'])
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->label('Estoque')
                    ->numeric()
                    ->sortable()
                    ->icon(fn ($record) => $record->current_stock <= $record->minimum_stock ? Phosphor::ArrowDownThin : null)
                    ->iconColor(fn ($record) => $record->current_stock <= $record->minimum_stock ? 'danger' : '')
                    ->color(fn ($record) => $record->current_stock <= $record->minimum_stock ? 'danger' : 'success'),
                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordUrl(null)
            ->recordAction('custom_view')
            ->recordActions([
                SimpleActions::getViewWithEditAndDelete(
                    width: Width::Large,
                    schemaCallback: fn ($schema) => ProductForm::configure($schema),
                    schemaViewCallback: fn (Schema $schema) => ProductInfolist::configure($schema),
                    actionCallback: fn (Model $record, array $data) => ProductService::update($record, $data),
                    model: Product::class,
                    recordName: 'Produto',
                    modal: false,
                    relations: ['photos']
                )
            ])
            ->toolbarActions([
            //    BulkActionGroup::make([
            //        DeleteBulkAction::make(),
            //        ForceDeleteBulkAction::make(),
            //        RestoreBulkAction::make(),
            //     ]),
            ]);
    }
}
