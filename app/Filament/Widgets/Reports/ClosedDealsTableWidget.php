<?php

namespace App\Filament\Widgets\Reports;

use App\Filament\Widgets\Concerns\HasDashboardScope;
use App\Models\Deal;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportHeaderAction;
use JeffersonGoncalves\FilamentExportAction\Actions\FilamentExportBulkAction;

class ClosedDealsTableWidget extends BaseWidget
{
    use HasDashboardScope;

    protected ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return 'Negócios no Período' . $this->getPeriodTitleSuffix();
    }

    public function getTableHeading(): ?string
    {
        return 'Negócios no Período' . $this->getPeriodTitleSuffix();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $period = $this->getSelectedPeriod();

                return Deal::query()
                    ->with(['client', 'user'])
                    ->whereBetween('created_at', [$period['start'], $period['end']])
                    ->tap(fn ($q) => $this->scopeByProfile($q))
                    ->tap(fn ($q) => $this->scopeByStatus($q));
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título do Negócio')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_value')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('actual_close_date')
                    ->label('Data de Fechamento')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export_deals')
                    ->label('Exportar Vendas')
                    ->fileName('relatorio_vendas_' . now()->format('Y-m-d')),
            ])
            ->bulkActions([
                FilamentExportBulkAction::make('export_selected')
                    ->label('Exportar Selecionados'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
