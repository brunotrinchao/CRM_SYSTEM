<?php

namespace Database\Seeders;

use App\Enums\ClientOrigin;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $firstUser = User::first();
        $userId = $firstUser ? $firstUser->id : 1;

        $clientsTemplate = [
            ['name' => 'João Silva', 'email' => 'joao.silva@example.com', 'phone' => '(11) 3456-7890', 'cellphone' => '(11) 98765-4321', 'origin' => ClientOrigin::GOOGLE->value, 'description' => 'Cliente pessoa física'],
            ['name' => 'Maria Oliveira', 'email' => 'maria.oliveira@example.com', 'phone' => '(21) 2233-4455', 'cellphone' => '(21) 99876-5432', 'origin' => ClientOrigin::INDICACAO->value, 'description' => 'Indicada pelo cliente João'],
            ['name' => 'Tech Solutions LTDA', 'email' => 'contato@techsolutions.com.br', 'phone' => '(31) 3222-1100', 'cellphone' => '(31) 99111-2233', 'origin' => ClientOrigin::SITE->value, 'description' => 'Empresa de TI, compra em volume'],
            ['name' => 'Ana Pereira', 'email' => 'ana.pereira@example.com', 'phone' => '(41) 3344-5566', 'cellphone' => '(41) 98877-6655', 'origin' => ClientOrigin::WHATSAPP->value, 'description' => 'Cliente recorrente'],
            ['name' => 'Roberto Carlos', 'email' => 'roberto.carlos@example.com', 'phone' => '(51) 3211-4433', 'cellphone' => '(51) 99776-5544', 'origin' => ClientOrigin::OLX->value, 'description' => 'Comprador frequente'],
            ['name' => 'Juliana Costa', 'email' => 'juliana.costa@example.com', 'phone' => '(71) 3322-1144', 'cellphone' => '(71) 98866-5544', 'origin' => ClientOrigin::FACEBOOK->value, 'description' => 'Lead de campanha social'],
            ['name' => 'Grupo Acqua S.A.', 'email' => 'atendimento@grupoacqua.com.br', 'phone' => '(11) 4004-9988', 'cellphone' => '(11) 97788-9900', 'origin' => ClientOrigin::SITE->value, 'description' => 'Grande conta industrial'],
            ['name' => 'Construtora Alfa', 'email' => 'vendas@construtoraalfa.com.br', 'phone' => '(31) 3300-8877', 'cellphone' => '(31) 98899-0011', 'origin' => ClientOrigin::INDICACAO->value, 'description' => 'Cliente corporativo civil'],
            ['name' => 'Logística Brasil', 'email' => 'contato@logisticabrasil.com.br', 'phone' => '(41) 3111-2233', 'cellphone' => '(41) 99222-3344', 'origin' => ClientOrigin::GOOGLE->value, 'description' => 'Operadora de transportes'],
            ['name' => 'Hospital São Lucas', 'email' => 'compras@hospitalsaolucas.med.br', 'phone' => '(51) 3344-1122', 'cellphone' => '(51) 99112-2334', 'origin' => ClientOrigin::WHATSAPP->value, 'description' => 'Rede hospitalar regional'],
        ];

        $mockAddresses = [
            [
                'street' => 'Av. Afonso Pena',
                'number' => '1500',
                'neighborhood' => 'Centro',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
                'zip_code' => '30130-002',
                'type' => 'COMMERCIAL',
                'reference' => 'Próximo à praça',
            ],
            [
                'street' => 'Rua das Flores',
                'number' => '321',
                'neighborhood' => 'Savassi',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
                'zip_code' => '30110-020',
                'type' => 'RESIDENCE',
                'reference' => 'Bloco B',
            ],
            [
                'street' => 'Av. Paulista',
                'number' => '1000',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01310-100',
                'type' => 'DELIVERY',
                'reference' => 'Portaria principal',
            ],
        ];

        foreach ($clientsTemplate as $clientData) {
            $client = Client::firstOrCreate(
                ['email' => $clientData['email']],
                [
                    ...$clientData,
                    'user_id' => $userId,
                ]
            );

            if ($client->addresses()->count() === 0) {
                $selectedAddress = collect($mockAddresses)->random();
                $client->addresses()->create(array_merge($selectedAddress, [
                    'user_id' => $userId,
                    'country' => 'Brasil',
                ]));
            }
        }
    }
}
