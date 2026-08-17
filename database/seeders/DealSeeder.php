<?php

namespace Database\Seeders;

use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Models\Client;
use App\Models\Deal;
use App\Models\DealNote;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory;

class DealSeeder extends Seeder
{
    public function run(): void
    {
        $admins = User::whereIn('profile', [UserProfile::ADMIN, UserProfile::MANAGER])->get();
        $sellers = User::where('profile', UserProfile::USER)->get();

        if ($sellers->isEmpty() || $admins->isEmpty()) {
            return;
        }

        $adminId = $admins->first()->id;
        $faker = Factory::create('pt_BR');
        $statuses = [DealStatus::NEGOTIATING->value, DealStatus::PENDING->value, DealStatus::WON->value, DealStatus::LOST->value];
        $clients = Client::all();
        $products = Product::all();

        if ($clients->isEmpty()) {
            return;
        }

        // 1. Gera negócios randômicos históricos para cada vendedor
        foreach ($sellers as $seller) {
            $count = rand(15, 25);

            for ($i = 0; $i < $count; $i++) {
                $status = $faker->randomElement($statuses);
                $createdAt = $faker->dateTimeBetween('-120 days', 'now');
                $client = $clients->random();

                $dealData = [
                    'created_by' => $adminId,
                    'user_id' => $seller->id,
                    'client_id' => $client->id,
                    'title' => 'Negócio ' . $faker->company,
                    'total_value' => 0.00,
                    'discount' => 0.00,
                    'status' => $status,
                    'probability' => match ($status) {
                        'WON' => 100,
                        'LOST' => 0,
                        'NEGOTIATING' => 60,
                        default => 40,
                    },
                    'notes' => $faker->sentence(),
                    'expected_close_date' => $faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
                    'created_at' => $createdAt,
                ];

                if ($status === 'WON' || $status === 'LOST') {
                    $dealData['actual_close_date'] = $faker->dateTimeBetween($createdAt, 'now');
                }

                if ($status === 'LOST') {
                    $dealData['loss_reason'] = $faker->randomElement(['Preço alto', 'Concorrente', 'Sem orçamento']);
                }

                $deal = Deal::create($dealData);

                $this->attachRandomProducts($deal, $products, $faker);
            }
        }

        // 2. Garante vendas concluídas no mês ATUAL para todos os vendedores (Vendas do Mês > 0)
        $startOfMonth = now()->startOfMonth();
        foreach ($sellers as $seller) {
            $wonThisMonthCount = rand(2, 5);
            for ($w = 1; $w <= $wonThisMonthCount; $w++) {
                $client = $clients->random();
                $created = $faker->dateTimeBetween($startOfMonth, 'now');
                $deal = Deal::create([
                    'created_by' => $adminId,
                    'user_id' => $seller->id,
                    'client_id' => $client->id,
                    'title' => "Venda Concluída #{$w} - " . $client->name,
                    'total_value' => 0.00,
                    'discount' => 0.00,
                    'status' => DealStatus::WON,
                    'probability' => 100,
                    'notes' => "Negócio fechado no mês atual",
                    'expected_close_date' => $created->format('Y-m-d'),
                    'actual_close_date' => $created->format('Y-m-d'),
                    'created_at' => $created,
                ]);

                $this->attachRandomProducts($deal, $products, $faker);
            }
        }

        // 3. Garante vendas recorrentes com clientes específicos
        $techClient = Client::where('name', 'like', '%Tech Solutions%')->first() ?? $clients->first();
        $joaoClient = Client::where('name', 'like', '%João Silva%')->first() ?? $clients->last();

        $sellerArray = $sellers->all();

        if (count($sellerArray) >= 2) {
            // Vendedor 1: 3 vendas fechadas para Tech Solutions
            for ($k = 1; $k <= 3; $k++) {
                $created = $faker->dateTimeBetween($startOfMonth, 'now');
                $deal = Deal::create([
                    'created_by' => $adminId,
                    'user_id' => $sellerArray[0]->id,
                    'client_id' => $techClient->id,
                    'title' => "Expansão de Licenças #{$k} - " . $techClient->name,
                    'total_value' => 0.00,
                    'discount' => 0.00,
                    'status' => DealStatus::WON,
                    'probability' => 100,
                    'notes' => "Venda de renovação/expansão número {$k}",
                    'expected_close_date' => $created->format('Y-m-d'),
                    'actual_close_date' => $created->format('Y-m-d'),
                    'created_at' => $created,
                ]);

                $this->attachRandomProducts($deal, $products, $faker);
            }

            // Vendedor 2: 2 vendas fechadas para Tech Solutions
            for ($k = 1; $k <= 2; $k++) {
                $created = $faker->dateTimeBetween($startOfMonth, 'now');
                $deal = Deal::create([
                    'created_by' => $adminId,
                    'user_id' => $sellerArray[1]->id,
                    'client_id' => $techClient->id,
                    'title' => "Consultoria Inicial #{$k} - " . $techClient->name,
                    'total_value' => 0.00,
                    'discount' => 0.00,
                    'status' => DealStatus::WON,
                    'probability' => 100,
                    'notes' => "Projeto encerrado com sucesso {$k}",
                    'expected_close_date' => $created->format('Y-m-d'),
                    'actual_close_date' => $created->format('Y-m-d'),
                    'created_at' => $created,
                ]);

                $this->attachRandomProducts($deal, $products, $faker);
            }

            // Vendedor 2: 2 vendas fechadas para João Silva
            for ($k = 1; $k <= 2; $k++) {
                $created = $faker->dateTimeBetween($startOfMonth, 'now');
                $deal = Deal::create([
                    'created_by' => $adminId,
                    'user_id' => $sellerArray[1]->id,
                    'client_id' => $joaoClient->id,
                    'title' => "Contrato Anual #{$k} - " . $joaoClient->name,
                    'total_value' => 0.00,
                    'discount' => 0.00,
                    'status' => DealStatus::WON,
                    'probability' => 100,
                    'notes' => "Fechamento contratual de produtos {$k}",
                    'expected_close_date' => $created->format('Y-m-d'),
                    'actual_close_date' => $created->format('Y-m-d'),
                    'created_at' => $created,
                ]);

                $this->attachRandomProducts($deal, $products, $faker);
            }
        }

        // 4. Cria histórico de contatos (DealNote) para todos os negócios ativos
        $activeDeals = Deal::whereIn('status', [DealStatus::PENDING, DealStatus::NEGOTIATING])->get();
        $channels = ['CALL', 'WHATSAPP', 'EMAIL', 'MEETING'];

        foreach ($activeDeals as $index => $deal) {
            $noteCount = rand(1, 3);
            
            $scenario = $index % 3; // 0 = Atrasado >24h, 1 = Pendente <24h/Hoje, 2 = Em dia/Futuro

            for ($n = 1; $n <= $noteCount; $n++) {
                $isLast = ($n === $noteCount);
                $contactDate = now()->subDays($noteCount - $n + 1)->subHours(rand(1, 8));

                if (! $isLast) {
                    $nextFollowUp = $contactDate->copy()->addDays(1);
                } else {
                    if ($scenario === 0) {
                        $nextFollowUp = now()->subHours(rand(28, 60));
                    } elseif ($scenario === 1) {
                        $nextFollowUp = now()->subHours(rand(2, 18));
                    } else {
                        $nextFollowUp = now()->addDays(rand(2, 7));
                    }
                }

                DealNote::create([
                    'deal_id' => $deal->id,
                    'user_id' => $deal->user_id,
                    'interaction_type' => $faker->randomElement($channels),
                    'content' => $faker->randomElement([
                        'Ligação realizada para alinhar requisitos da proposta.',
                        'Reunião remota com o tomador de decisão da empresa.',
                        'Envio de orçamento revisado com condições de pagamento negociadas.',
                        'Contato via WhatsApp para esclarecer dúvidas técnicas do produto.',
                        'Apresentação da solução e envio do contrato minutas.',
                    ]),
                    'contact_date' => $contactDate,
                    'next_follow_up_date' => $nextFollowUp,
                    'next_action' => $faker->randomElement([
                        'Retornar ligação para confirmar aceite da proposta',
                        'Enviar minuta de contrato atualizada',
                        'Agendar demonstração prática com equipe técnica',
                        'Verificar aprovação do limite de crédito',
                    ]),
                ]);
            }

            $deal->update([
                'last_contact_date' => now()->subDays(1),
            ]);
        }
    }

    /**
     * Associa produtos ao negócio e calcula matematicamente o total_value e o discount.
     */
    private function attachRandomProducts(Deal $deal, $products, $faker): void
    {
        if ($products->isEmpty()) {
            return;
        }

        $selectedProducts = $products->random(min($products->count(), rand(1, 3)));
        
        $dealTotalValue = 0.00;
        $dealTotalDiscount = 0.00;

        foreach ($selectedProducts as $product) {
            $quantity = rand(1, 4);
            $unitPrice = (float) $product->price;
            $subtotal = $unitPrice * $quantity;
            
            // Desconto condicional no item (0 ou valor razoável)
            $discount = (rand(1, 100) <= 30) ? (float) (rand(1, 5) * 20) : 0.00;
            if ($discount >= $subtotal) {
                $discount = 0.00;
            }

            $itemTotalPrice = round(max(0, $subtotal - $discount), 2);

            $deal->products()->attach($product->id, [
                'quantity' => $quantity,
                'discount' => $discount,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotalPrice,
            ]);

            $dealTotalValue += $itemTotalPrice;
            $dealTotalDiscount += $discount;
        }

        $deal->update([
            'discount' => round($dealTotalDiscount, 2),
            'total_value' => round($dealTotalValue, 2),
        ]);
    }
}
