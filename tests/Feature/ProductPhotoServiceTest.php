<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPhotoServiceTest extends TestCase
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

    private function baseData(): array
    {
        $category = Category::create([
            'name' => 'Categoria Teste',
            'user_id' => $this->user->id,
        ]);

        return [
            'name' => 'Produto Teste',
            'category_id' => $category->id,
            'price' => 150.00,
            'current_stock' => 10,
            'minimum_stock' => 1,
            'active' => true,
        ];
    }

    public function test_create_saves_product_photos_with_string_paths(): void
    {
        $this->actingAs($this->user);

        $data = array_merge($this->baseData(), [
            'photos' => ['avatars/1.jpg', 'avatars/2.jpg'],
        ]);

        $product = ProductService::create($data);

        $this->assertSame(1, Product::count());
        $this->assertSame(2, $product->photos()->count());
        $this->assertSame($this->user->id, $product->photos()->first()->user_id);
        $this->assertSame('avatars/1.jpg', $product->photos()->first()->image);
    }

    public function test_update_syncs_photos_add_remove_and_keeps_unchanged(): void
    {
        $this->actingAs($this->user);

        $data = array_merge($this->baseData(), [
            'photos' => ['avatars/1.jpg', 'avatars/2.jpg'],
        ]);

        $product = ProductService::create($data);

        // remove 1.jpg, mantém 2.jpg, adiciona 3.jpg
        ProductService::update($product, array_merge($this->baseData(), [
            'photos' => ['avatars/2.jpg', 'avatars/3.jpg'],
        ]));

        $this->assertSame(
            ['avatars/2.jpg', 'avatars/3.jpg'],
            $product->photos()->orderBy('id')->pluck('image')->all()
        );
    }

    public function test_update_without_photos_keeps_existing(): void
    {
        $this->actingAs($this->user);

        $data = array_merge($this->baseData(), [
            'photos' => ['avatars/1.jpg'],
        ]);

        $product = ProductService::create($data);

        ProductService::update($product, $this->baseData());

        $this->assertSame(
            ['avatars/1.jpg'],
            $product->photos()->pluck('image')->all()
        );
    }
}
