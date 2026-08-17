<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $productsTemplate = [
            ['name' => 'Smartphone Galaxy A54', 'sku' => 'ELT-001', 'price' => 1899.90, 'current_stock' => 25, 'minimum_stock' => 5, 'active' => true, 'observation' => 'Tela 6.4", 128GB', 'category' => 'Eletrônicos'],
            ['name' => 'Notebook Dell Inspiron', 'sku' => 'ELT-002', 'price' => 4299.00, 'current_stock' => 8, 'minimum_stock' => 2, 'active' => true, 'observation' => 'i5, 8GB, 256GB SSD', 'category' => 'Eletrônicos'],
            ['name' => 'Fone Bluetooth JBL', 'sku' => 'ACC-001', 'price' => 249.90, 'current_stock' => 40, 'minimum_stock' => 10, 'active' => true, 'observation' => 'Cancelamento de ruído', 'category' => 'Acessórios'],
            ['name' => 'Capa de Celular Silicone', 'sku' => 'ACC-002', 'price' => 39.90, 'current_stock' => 60, 'minimum_stock' => 15, 'active' => true, 'observation' => '', 'category' => 'Acessórios'],
            ['name' => 'Mesa de Escritório 1.40m', 'sku' => 'MOV-001', 'price' => 899.00, 'current_stock' => 5, 'minimum_stock' => 1, 'active' => true, 'observation' => 'MDP, cor carvalho', 'category' => 'Móveis'],
            ['name' => 'Camiseta Básica Algodão', 'sku' => 'VES-001', 'price' => 59.90, 'current_stock' => 100, 'minimum_stock' => 20, 'active' => true, 'observation' => 'Tamanho P ao GG', 'category' => 'Vestuário'],
        ];

        foreach ($users as $user) {
            $categoryIds = Category::where('user_id', $user->id)->pluck('id', 'name');

            foreach ($productsTemplate as $product) {
                $categoryName = $product['category'];
                $productData = $product;
                unset($productData['category']);

                // Sku único por usuário ou global
                $sku = $user->id === 1 ? $productData['sku'] : $productData['sku'] . '-' . $user->id;

                Product::firstOrCreate(
                    ['user_id' => $user->id, 'sku' => $sku],
                    [
                        ...$productData,
                        'sku' => $sku,
                        'category_id' => $categoryIds[$categoryName] ?? null,
                    ]
                );
            }
        }
    }
}
