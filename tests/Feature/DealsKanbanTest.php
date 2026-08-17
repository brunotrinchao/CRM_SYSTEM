<?php

namespace Tests\Feature;

use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Livewire\DealsKanban;
use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DealsKanbanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kanban_component_renders_successfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->assertStatus(200);
    }

    public function test_moving_pending_deal_to_negotiating_succeeds(): void
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
            'title' => 'Negócio Pendente',
            'status' => DealStatus::PENDING,
            'total_value' => 1000,
        ]);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::NEGOTIATING->value);

        $this->assertEquals(DealStatus::NEGOTIATING, $deal->fresh()->status);
    }

    public function test_moving_pending_deal_to_cancelled_opens_confirmation_modal_and_cancels_on_confirm(): void
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
            'title' => 'Negócio Pendente',
            'status' => DealStatus::PENDING,
            'total_value' => 1000,
        ]);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::CANCELLED->value)
            ->assertSet('showCancelModal', true)
            ->call('executeCancelDeal');

        $this->assertEquals(DealStatus::CANCELLED, $deal->fresh()->status);
    }

    public function test_moving_negotiating_deal_to_won_succeeds(): void
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
            'title' => 'Negócio em Negociação',
            'status' => DealStatus::NEGOTIATING,
            'total_value' => 2000,
        ]);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::WON->value);

        $this->assertEquals(DealStatus::WON, $deal->fresh()->status);
    }

    public function test_moving_negotiating_deal_to_lost_opens_modal_and_executes_lost(): void
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
            'title' => 'Negócio em Negociação',
            'status' => DealStatus::NEGOTIATING,
            'total_value' => 2000,
        ]);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::LOST->value)
            ->assertSet('showLostModal', true)
            ->call('executeLostDeal');

        $this->assertEquals(DealStatus::LOST, $deal->fresh()->status);
    }

    public function test_cancelled_deal_cannot_be_moved(): void
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
            'title' => 'Negócio Cancelado',
            'status' => DealStatus::CANCELLED,
            'total_value' => 2000,
        ]);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::NEGOTIATING->value);

        $this->assertEquals(DealStatus::CANCELLED, $deal->fresh()->status);
    }
}
