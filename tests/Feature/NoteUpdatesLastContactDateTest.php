<?php

namespace Tests\Feature;

use App\Enums\ChannelNote;
use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Models\Client;
use App\Models\Deal;
use App\Models\DealNote;
use App\Models\User;
use App\Services\DealNoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteUpdatesLastContactDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_note_updates_deal_last_contact_date_via_observer(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Teste',
            'status' => DealStatus::PENDING,
            'total_value' => 1000,
            'last_contact_date' => null,
        ]);

        $contactDate = now()->subHours(2);

        DealNote::create([
            'user_id' => $user->id,
            'deal_id' => $deal->id,
            'interaction_type' => ChannelNote::WHATSAPP,
            'content' => 'Contato realizado via WhatsApp',
            'contact_date' => $contactDate,
        ]);

        $deal->refresh();

        $this->assertNotNull($deal->last_contact_date);
        $this->assertEquals(
            $contactDate->format('Y-m-d'),
            $deal->last_contact_date->format('Y-m-d')
        );
    }

    public function test_creating_a_note_updates_deal_last_contact_date_via_service(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste 2',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Teste 2',
            'status' => DealStatus::PENDING,
            'total_value' => 1500,
            'last_contact_date' => null,
        ]);

        $this->actingAs($user);

        $contactDate = now()->subDays(1);

        DealNoteService::create([
            'deal_id' => $deal->id,
            'interaction_type' => ChannelNote::CALL,
            'content' => 'Ligação realizada',
            'contact_date' => $contactDate,
        ]);

        $deal->refresh();

        $this->assertNotNull($deal->last_contact_date);
        $this->assertEquals(
            $contactDate->format('Y-m-d'),
            $deal->last_contact_date->format('Y-m-d')
        );
    }
}
