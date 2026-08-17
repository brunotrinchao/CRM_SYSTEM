<?php

namespace Tests\Feature;

use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Livewire\PendingContactsHeaderBadge;
use App\Models\Client;
use App\Models\Deal;
use App\Models\DealNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PendingContactsHeaderBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_badge_renders_correct_pending_count(): void
    {
        $seller = User::create([
            'name' => 'Seller Test',
            'email' => 'seller_badge@test.com',
            'password' => bcrypt('password'),
            'profile' => UserProfile::USER,
        ]);

        $client = Client::create([
            'user_id' => $seller->id,
            'name' => 'Client Test',
            'email' => 'client_badge@test.com',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $seller->id,
            'created_by' => $seller->id,
            'client_id' => $client->id,
            'title' => 'Pending Deal Badge',
            'total_value' => 5000,
            'status' => DealStatus::PENDING,
            'probability' => 50,
        ]);

        DealNote::create([
            'deal_id' => $deal->id,
            'user_id' => $seller->id,
            'interaction_type' => 'CALL',
            'content' => 'Teste contato atrasado',
            'next_follow_up_date' => now()->subHours(10),
            'contact_date' => now()->subHours(15),
        ]);

        $this->actingAs($seller);

        Livewire::test(PendingContactsHeaderBadge::class)
            ->assertSee('Contatos pendentes:')
            ->assertSee('1');
    }
}
