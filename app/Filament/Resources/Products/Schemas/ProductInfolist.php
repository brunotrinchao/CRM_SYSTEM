<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Components\Card;
use App\Components\Infolist\Date;
use App\Components\Infolist\Icon;
use App\Components\Infolist\Image;
use App\Components\Infolist\Money;
use App\Components\Infolist\Repeater;
use App\Components\Infolist\Text;
use App\Models\ProductPhoto;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações do Produto')
                    ->icon(Heroicon::OutlinedCube)
                    ->iconColor(Color::Blue)
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nome do Produto'),
                                TextEntry::make('sku')
                                    ->label('SKU / Código'),
                                TextEntry::make('category.name')
                                    ->label('Categoria'),
                                IconEntry::make('active')
                                    ->label('Ativo')
                                    ->boolean() // Informa ao Filament que é um campo booleano
                                    ->trueColor(Color::Green)
                                    ->falseColor(Color::Red)
                            ])
                    ]),
                Section::make('Precificação e Estoque Físico')
                    ->icon(Heroicon::OutlinedCurrencyDollar)
                    ->iconColor(Color::Blue)
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Money::make('price', 'Preço Unitário'),
                                TextEntry::make('current_stock')
                                    ->label('Estoque Atual')
                                    ->numeric(),
                                TextEntry::make('minimum_stock')
                                    ->label('Estoque Mínimo')
                                    ->numeric(),
                                TextEntry::make('total_stock_value')
                                    ->label('Valoração Total em Estoque')
                                    ->getStateUsing(function ($record) {
                                        $totalValue = $record->current_stock * $record->price;

                                        return "R$ " . number_format($totalValue, 2, ',', '.');
                                    })
                            ])
                    ]),
                Section::make('Observações e Especificações')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        TextEntry::make('observation')
                            ->label('Descrição / Observação do Produto')
                    ]),
                Section::make(fn (Model $record) => 'Fotos Anexadas (' . $record->photos()->count() . ')')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->iconColor(Color::Blue)
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Repeater::make('photos')
                            ->hiddenLabel()
                            ->schema([
                                ImageEntry::make('image')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record) => \App\Models\ProductPhoto::cleanImageUrl($record->image ?? $record->url))
                                    ->imageWidth(80)
                                    ->imageHeight(80)
                                    ->extraImgAttributes([
                                        'loading' => 'lazy',
                                        'style' => 'padding:0'
                                    ])
                                    ->square()
                                    ->simpleLightbox(),
                            ])
                            ->grid(3)
                    ]),
                Section::make('Metadados de Auditoria')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Criado em')
                                    ->date('d/m/Y h:i:s'),
                                TextEntry::make('updated_at')
                                    ->label('Atualizado em')
                                    ->date('d/m/Y h:i:s'),
                            ])
                    ]),
            ]);
    }
}
