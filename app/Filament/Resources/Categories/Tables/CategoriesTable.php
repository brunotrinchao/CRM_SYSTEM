<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Categories\Schemas\CategoryInfolist;
use App\Models\Category;
use App\Services\CategoryService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('active')
                ->label('Ativo')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->recordUrl(null)
            ->recordAction('custom_view') 
            ->recordActions([
                // ViewAction::make(),
                SimpleActions::getViewWithEditAndDelete(
                    width: Width::Large,
                    schemaCallback: fn ($schema) => CategoryForm::configure($schema),
                    schemaViewCallback: fn (Schema $schema) => CategoryInfolist::configure($schema),
                    actionCallback: fn (Model $record, array $data) => CategoryService::update($record, $data),
                    model: Category::class,
                    recordName: 'Categoria',
                    modal: false,
                )
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
