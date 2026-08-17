<?php

namespace App\Filament\Widgets;

use App\Models\User; // Ou Model de Vendedores
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSellersWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Ranking de Vendedores';

    protected int|string|array $columnSpan = [
        'lg' => 1,
    ];

    protected ?string $maxHeight = 'auto';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->withCount('deals') // Necessário para preencher 'deals_count'
                    ->withSum('deals', 'total_value') // Gera o atributo 'deals_sum_total_value'
                    ->orderByDesc('deals_sum_total_value') // Ordena pelo maior total vendido
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Vendedor'),

                Tables\Columns\TextColumn::make('deals_count')
                    ->label('Negócios Fechados'),

                Tables\Columns\TextColumn::make('deals_sum_total_value') // Nome correto gerado pelo withSum
                    ->label('Total Vendido')
                    ->money('BRL'),
            ])
            ->paginated(false);
    }
}
