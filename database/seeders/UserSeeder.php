<?php

namespace Database\Seeders;

use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Garante o usuário administrador principal e cria usuários vendedores (profile = USER).
     */
    public function run(): void
    {
        // 1. Administrador Principal
        User::firstOrCreate(
            ['email' => env('CRM_ADMIN_EMAIL', 'brunotrinchao@gmail.com')],
            [
                'name' => env('CRM_ADMIN_NAME', 'ADMIN'),
                'password' => Hash::make(env('CRM_ADMIN_PASSWORD', 'admin')),
                'profile' => UserProfile::ADMIN->value,
            ]
        );

        // 2. Vendedores (Profile = USER)
        $sellers = [
            ['name' => 'Carlos Santos', 'email' => 'carlos.vendedor@example.com'],
            ['name' => 'Ana Lima', 'email' => 'ana.vendedora@example.com'],
            ['name' => 'Lucas Ferreira', 'email' => 'lucas.vendedor@example.com'],
            ['name' => 'Beatriz Rocha', 'email' => 'beatriz.vendedora@example.com'],
            ['name' => 'Mariana Costa', 'email' => 'mariana.vendedora@example.com'],
        ];

        foreach ($sellers as $sellerData) {
            User::firstOrCreate(
                ['email' => $sellerData['email']],
                [
                    'name' => $sellerData['name'],
                    'password' => Hash::make('password'),
                    'profile' => UserProfile::USER->value,
                ]
            );
        }
    }
}
