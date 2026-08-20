<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Models\Product;
use App\Services\ProductService;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SimpleActions::getCreateModal(
                width: Width::Large,
                schemaCallback: fn($schema) => ProductForm::configure($schema),
                actionCallback: fn(array $data) => ProductService::create($data),
                recordName: 'Produto',
                model: Product::class,
                modal: false,
                name: 'create_product_modal',
            ),
        ];
    }
}
