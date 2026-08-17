<?php

namespace App\Filament\Widgets;

use App\Models\Product; // Ajuste para o seu Model de Produtos
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CriticalStockTableWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Alerta de Estoque Crítico';

    protected int | string | array $columnSpan = [
        'lg' => 1,
    ];

    public static function canView(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()?->profile === \App\Enums\UserProfile::ADMIN;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()->where('current_stock', '<=', 'minimum_stock')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Produto'),
                Tables\Columns\TextColumn::make('current_stock')->label('Estoque Atual')->color('danger'),
                Tables\Columns\TextColumn::make('minimum_stock')->label('Estoque Mínimo'),
            ])
            ->paginated(false);
    }
}