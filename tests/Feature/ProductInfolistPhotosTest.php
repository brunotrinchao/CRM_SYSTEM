<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use SolutionForest\FilamentSimpleLightBox\SimpleLightBoxPlugin;
use Tests\TestCase;

class ProductInfolistPhotosTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $panel = Filament::getPanel('admin');
        if ($panel) {
            SimpleLightBoxPlugin::make()->boot($panel);
        }

        $this->user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'profile' => 'ADMIN',
        ]);
    }

    private function productWithPhotos(array $urls): Product
    {
        $category = Category::create([
            'name' => 'Cat',
            'user_id' => $this->user->id,
        ]);

        $product = Product::create([
            'name' => 'Produto Fotos',
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'price' => 10.00,
            'current_stock' => 1,
            'minimum_stock' => 0,
            'active' => true,
        ]);

        foreach ($urls as $url) {
            $product->photos()->create([
                'user_id' => $this->user->id,
                'image' => $url,
            ]);
        }

        return $product;
    }

    public function test_view_product_renders_photos_in_infolist(): void
    {
        $this->actingAs($this->user);

        $product = $this->productWithPhotos([
            'https://res.cloudinary.com/x/image/upload/v1/CRM/foto1.png',
            'https://res.cloudinary.com/x/image/upload/v1/CRM/foto2.png',
        ]);

        $html = Livewire::test(ViewProduct::class, ['record' => $product->getKey()])
            ->assertOk()
            ->html();

        // card de fotos presente
        $this->assertStringContainsString('Fotos', $html);
        // URLs das imagens renderizadas (uma ao lado da outra)
        $this->assertStringContainsString('foto1.png', $html);
        $this->assertStringContainsString('foto2.png', $html);
        // layout em grade (lado a lado): grid de 3 colunas
        $this->assertStringContainsString('fi-in-repeatable', $html);
        // lightbox (clique abre ampliado)
        $this->assertStringContainsString('simple-light-box-img-indicator', $html);
        // label oculto (sem título visível sobre a imagem)
        $this->assertStringContainsString('fi-sr-only', $html);
    }

    public function test_view_product_without_photos_still_renders(): void
    {
        $this->actingAs($this->user);

        $product = $this->productWithPhotos([]);

        Livewire::test(ViewProduct::class, ['record' => $product->getKey()])
            ->assertOk();
    }
}
