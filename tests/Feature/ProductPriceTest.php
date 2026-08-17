<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductPriceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'profile' => 'ADMIN',
        ]);
    }

    public function test_currency_input_saves_real_in_database(): void
    {
        $this->actingAs($this->user);

        $category = Category::create(['name' => 'Geral', 'user_id' => $this->user->id]);

        // o plugin trabalha em centavos no state; digitando 150,00
        // o JS envia 15000 (centavos). O dehydrateStateUsing deve
        // gravar 150.00 (reais) na coluna decimal(10,2).
        Livewire::test(CreateProduct::class)
            ->set('data.category_id', $category->id)
            ->set('data.name', 'Produto Teste')
            ->set('data.sku', 'SKU-123')
            ->set('data.price', 15000) // centavos: R$ 150,00
            ->set('data.current_stock', 10)
            ->set('data.minimum_stock', 0)
            ->set('data.active', true)
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('sku', 'SKU-123')->first();
        $this->assertNotNull($product);
        $this->assertSame(150.00, (float) $product->price);
    }

    public function test_currency_input_rounds_fractional_centavos(): void
    {
        $this->actingAs($this->user);

        $category = Category::create(['name' => 'Geral', 'user_id' => $this->user->id]);

        Livewire::test(CreateProduct::class)
            ->set('data.category_id', $category->id)
            ->set('data.name', 'Produto Teste 2')
            ->set('data.sku', 'SKU-456')
            ->set('data.price', 15001) // R$ 150,01
            ->set('data.current_stock', 5)
            ->set('data.minimum_stock', 0)
            ->set('data.active', true)
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('sku', 'SKU-456')->first();
        $this->assertNotNull($product);
        $this->assertSame(150.01, (float) $product->price);
    }
}
