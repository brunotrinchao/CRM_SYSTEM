<?php

namespace App\Filament\Resources\Deals;

use App\Filament\Resources\Deals\Pages\CreateDeal;
use App\Filament\Resources\Deals\Pages\EditDeal;
use App\Filament\Resources\Deals\Pages\ListDeals;
use App\Filament\Resources\Deals\Pages\ViewDeal;
use App\Filament\Resources\Deals\Schemas\DealForm;
use App\Filament\Resources\Deals\Schemas\DealInfolist;
use App\Filament\Resources\Deals\Tables\DealsTable;
use App\Models\Deal;
use App\Models\DiscountRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DealResource extends Resource
{
    protected static ?string $model = Deal::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::HandshakeDuotone;

    protected static ?string $navigationLabel = 'Negócios';
    protected static ?string $modelLabel = 'Negócio';
    protected static ?string $pluralModelLabel = 'Negócios';
    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
{
    return [
        'title', 
        'notes', 
        'user.name', 
        'client.name', 
        'loss_reason',
        'products.name',
    ];
}

    #[Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Cliente' => $record->client?->name ?? 'N/D',
            'Responsável' => $record->user?->name ?? 'N/D',
            'Status' => $record->status?->getLabel() ?? $record->status?->value ?? 'N/D',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Conta os DiscountRequests com status pendente relacionados através do model Deal
        $count = DiscountRequest::where('status', 'pending')->count();

        // Retorna null se não houver registros para não exibir badge vazio
        return $count > 0 ? (string) $count : null;
    }

    // Define a cor do badge (ex: alerta amarelo/laranja para pendências)
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary'; // Opções: 'danger', 'warning', 'success', 'primary'
    }
    public static function form(Schema $schema): Schema
    {
        return DealForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DealInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DealsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeals::route('/'),
            'create' => CreateDeal::route('/create'),
            'view' => ViewDeal::route('/{record}'),
            'edit' => EditDeal::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
