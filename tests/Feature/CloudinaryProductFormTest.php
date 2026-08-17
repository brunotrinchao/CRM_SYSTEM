<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CloudinaryProductFormTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'profile' => 'ADMIN',
        ]);

        $this->category = Category::create([
            'name' => 'Categoria Teste',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_product_form_mounts_without_type_error(): void
    {
        $this->actingAs($this->user);

        // montar o form (CreateProduct) não deve lançar TypeError
        // (regressão: closure do saveUploadedFileUsing tipava TemporaryUploadedFile errado)
        Livewire::test(CreateProduct::class)
            ->assertOk()
            ->assertFormFieldExists('photos');
    }

    public function test_edit_product_form_mounts_without_type_error(): void
    {
        $this->actingAs($this->user);

        $product = \App\Models\Product::create([
            'name' => 'Produto Edit',
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'price' => 10.00,
            'current_stock' => 1,
            'minimum_stock' => 0,
            'active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\Products\Pages\EditProduct::class, ['record' => $product->getKey()])
            ->assertOk()
            ->assertFormFieldExists('photos');
    }
}
