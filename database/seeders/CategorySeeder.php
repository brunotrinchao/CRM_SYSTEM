<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Eletrônicos', 'description' => 'Produtos eletrônicos em geral', 'active' => true],
            ['name' => 'Acessórios', 'description' => 'Acessórios e periféricos', 'active' => true],
            ['name' => 'Móveis', 'description' => 'Móveis para casa e escritório', 'active' => true],
            ['name' => 'Vestuário', 'description' => 'Roupas e calçados', 'active' => true],
            ['name' => 'Serviços', 'description' => 'Consultorias e serviços técnicos', 'active' => true],
        ];

        $users = User::all();

        foreach ($users as $user) {
            foreach ($categories as $category) {
                Category::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $category['name']],
                    $category
                );
            }
        }
    }
}
