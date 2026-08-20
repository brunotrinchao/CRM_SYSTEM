<?php

namespace Tests\Feature;

use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Filament\Resources\Deals\Schemas\DealInfolist;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Product;
use App\Models\User;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealInfolistFinancialDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_infolist_financial_data_calculates_subtotal_discount_and_total(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Financeiro',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Financeiro',
            'status' => DealStatus::NEGOTIATING,
            'discount' => 200,
            'total_value' => 1800,
        ]);

        $category = \App\Models\Category::create(['user_id' => $user->id, 'name' => 'Cat A']);

        $product1 = Product::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Produto A',
            'price' => 1000,
        ]);

        $product2 = Product::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Produto B',
            'price' => 500,
        ]);

        $deal->products()->attach([
            $product1->id => ['quantity' => 1, 'unit_price' => 1000, 'total_price' => 1000],
            $product2->id => ['quantity' => 2, 'unit_price' => 500, 'total_price' => 1000],
        ]);

        $this->actingAs($user);

        $financials = DealInfolist::calculateFinancials($deal);

        $this->assertEquals(2000.0, $financials['subtotal']);
        $this->assertEquals(200.0, $financials['discount']);
        $this->assertEquals(1800.0, $financials['total']);

        $schema = DealInfolist::configure(new Schema());
        $components = $schema->getComponents();

        $this->assertNotEmpty($components);
    }
}
