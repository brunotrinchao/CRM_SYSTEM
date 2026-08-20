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

class DealNoteActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_a_deal_note_modifies_data(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Nota',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Nota',
            'status' => DealStatus::NEGOTIATING,
            'total_value' => 1000,
        ]);

        $note = DealNote::create([
            'user_id' => $user->id,
            'deal_id' => $deal->id,
            'interaction_type' => ChannelNote::WHATSAPP,
            'content' => 'Conteúdo inicial',
            'contact_date' => now(),
        ]);

        $this->actingAs($user);

        $updated = DealNoteService::update($note, [
            'content' => 'Conteúdo atualizado',
            'interaction_type' => ChannelNote::CALL->value,
        ]);

        $this->assertEquals('Conteúdo atualizado', $updated->fresh()->content);
    }

    public function test_deleting_a_deal_note(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Nota Delete',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Nota Delete',
            'status' => DealStatus::NEGOTIATING,
            'total_value' => 1000,
        ]);

        $note = DealNote::create([
            'user_id' => $user->id,
            'deal_id' => $deal->id,
            'interaction_type' => ChannelNote::EMAIL,
            'content' => 'Para apagar',
            'contact_date' => now(),
        ]);

        $this->actingAs($user);

        $note->delete();

        $this->assertSoftDeleted($note);
    }

    public function test_cannot_update_note_when_deal_status_is_won_or_lost(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Status Fechado',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Ganho',
            'status' => DealStatus::WON,
            'total_value' => 1000,
        ]);

        $note = DealNote::create([
            'user_id' => $user->id,
            'deal_id' => $deal->id,
            'interaction_type' => ChannelNote::WHATSAPP,
            'content' => 'Nota em negócio ganho',
            'contact_date' => now(),
        ]);

        $this->actingAs($user);

        $this->expectException(\InvalidArgumentException::class);

        DealNoteService::update($note, [
            'content' => 'Tentativa de alteração',
        ]);
    }
}
