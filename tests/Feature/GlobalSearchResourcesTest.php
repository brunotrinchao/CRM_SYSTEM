<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Deals\DealResource;
use App\Filament\Resources\Products\ProductResource;
use Tests\TestCase;

class GlobalSearchResourcesTest extends TestCase
{
    public function test_resources_have_record_title_attribute_configured(): void
    {
        $this->assertEquals('name', ClientResource::getRecordTitleAttribute());
        $this->assertEquals('title', DealResource::getRecordTitleAttribute());
        $this->assertEquals('name', ProductResource::getRecordTitleAttribute());
        $this->assertEquals('name', CategoryResource::getRecordTitleAttribute());
    }

    public function test_resources_have_searchable_attributes_configured(): void
    {
        $this->assertContains('cellphone', ClientResource::getGloballySearchableAttributes());
        $this->assertNotEmpty(DealResource::getGloballySearchableAttributes());
        $this->assertNotEmpty(ProductResource::getGloballySearchableAttributes());
        $this->assertNotEmpty(CategoryResource::getGloballySearchableAttributes());
    }
}
