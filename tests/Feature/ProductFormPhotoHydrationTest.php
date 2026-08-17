<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFormPhotoHydrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_product_hydrates_existing_photo_urls_into_form_state(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['user_id' => $user->id, 'name' => 'Categoria Teste']);

        $product = Product::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Produto de Teste',
            'sku' => 'PROD-123',
            'price' => 99.90,
            'current_stock' => 10,
            'minimum_stock' => 2,
            'active' => true,
        ]);

        $product->photos()->createMany([
            ['user_id' => $user->id, 'image' => 'https://res.cloudinary.com/demo/image/upload/v1/products/img1.jpg'],
            ['user_id' => $user->id, 'image' => 'https://res.cloudinary.com/demo/image/upload/v1/products/img2.jpg'],
        ]);

        $this->actingAs($user);

        $photos = $product->fresh()->photos->pluck('image')->all();

        $this->assertCount(2, $photos);
        $this->assertContains('https://res.cloudinary.com/demo/image/upload/v1/products/img1.jpg', $photos);
        $this->assertContains('https://res.cloudinary.com/demo/image/upload/v1/products/img2.jpg', $photos);
    }
}
