<?php

namespace App\Filament\Widgets;

use App\Enums\DealStatus;
use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal; // Ajuste para o seu Model de Negócios/Oportunidades
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentDealsWidget extends BaseWidget
{
    use HasDashboardScope;
    protected static ?string $heading = 'Negócios Recentes';

    protected int | string | array $columnSpan = [
        'lg' => 3,
    ];

    public function table(Table $table): Table
    {
        $period = $this->getSelectedPeriod();
        
        return $table
            ->query(
                Deal::query()
                ->whereBetween('created_at', [$period['start'], $period['end']])
                ->latest()
                ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Oportunidade'),
                Tables\Columns\TextColumn::make('client.name')->label('Cliente'),
                Tables\Columns\TextColumn::make('total_value')->label('Valor')->money('BRL'),
                Tables\Columns\BadgeColumn::make('status')->label('Estágio')->color(fn ($record) => $record->status->color()),
            ])
            ->paginated(false);
    }
}