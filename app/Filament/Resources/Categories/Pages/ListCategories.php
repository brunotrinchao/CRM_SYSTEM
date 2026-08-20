<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Models\Category;
use App\Services\CategoryService;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SimpleActions::getCreateModal(
                width: Width::Large,
                schemaCallback: fn ($schema) => CategoryForm::configure($schema),
                actionCallback: fn (array $data) => CategoryService::create($data),
                recordName: 'Categoria',
                model: Category::class,
                modal: false,
                name: 'create_category_modal',
            ),
        ];
    }
}
