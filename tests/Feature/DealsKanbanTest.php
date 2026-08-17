<?php

namespace Tests\Feature;

use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Enums\UserProfile;
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

    private function makeDeal(User $user, DealStatus $status): Deal
    {
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'origin' => ClientOrigin::SITE,
        ]);

        return Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Teste',
            'status' => $status,
            'total_value' => 1000,
        ]);
    }

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
        $deal = $this->makeDeal($user, DealStatus::PENDING);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::NEGOTIATING->value);

        $this->assertEquals(DealStatus::NEGOTIATING, $deal->fresh()->status);
    }

    public function test_moving_pending_deal_to_won_or_lost_is_not_allowed(): void
    {
        $user = User::factory()->create();
        $deal = $this->makeDeal($user, DealStatus::PENDING);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::WON->value)
            ->assertActionNotMounted('change_deal_status');

        $this->assertEquals(DealStatus::PENDING, $deal->fresh()->status);
    }

    public function test_moving_pending_deal_to_cancelled_requires_confirmation(): void
    {
        $user = User::factory()->create();
        $deal = $this->makeDeal($user, DealStatus::PENDING);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::CANCELLED->value)
            ->assertActionMounted('change_deal_status');

        // Sem confirmar, o status não muda.
        $this->assertEquals(DealStatus::PENDING, $deal->fresh()->status);
    }

    public function test_moving_negotiating_deal_to_won_requires_confirmation_and_succeeds_on_confirm(): void
    {
        $user = User::factory()->create();
        $deal = $this->makeDeal($user, DealStatus::NEGOTIATING);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::WON->value)
            ->assertActionMounted('change_deal_status')
            ->callMountedAction();

        $this->assertEquals(DealStatus::WON, $deal->fresh()->status);
    }

    public function test_moving_negotiating_deal_to_lost_requires_confirmation_and_succeeds_on_confirm(): void
    {
        $user = User::factory()->create();
        $deal = $this->makeDeal($user, DealStatus::NEGOTIATING);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::LOST->value)
            ->assertActionMounted('change_deal_status')
            ->callMountedAction();

        $this->assertEquals(DealStatus::LOST, $deal->fresh()->status);
    }

    public function test_moving_negotiating_deal_to_cancelled_requires_confirmation_and_succeeds_on_confirm(): void
    {
        $user = User::factory()->create();
        $deal = $this->makeDeal($user, DealStatus::NEGOTIATING);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::CANCELLED->value)
            ->assertActionMounted('change_deal_status')
            ->callMountedAction();

        $this->assertEquals(DealStatus::CANCELLED, $deal->fresh()->status);
    }

    public function test_moving_negotiating_deal_back_to_pending_is_not_allowed(): void
    {
        $user = User::factory()->create();
        $deal = $this->makeDeal($user, DealStatus::NEGOTIATING);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::PENDING->value)
            ->assertActionNotMounted('change_deal_status');

        $this->assertEquals(DealStatus::NEGOTIATING, $deal->fresh()->status);
    }

    public function test_finished_deal_cannot_be_moved_by_user_profile(): void
    {
        $user = User::factory()->create(['profile' => UserProfile::USER]);
        $deal = $this->makeDeal($user, DealStatus::CANCELLED);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::NEGOTIATING->value)
            ->assertActionNotMounted('change_deal_status');

        $this->assertEquals(DealStatus::CANCELLED, $deal->fresh()->status);
    }

    public function test_finished_deal_can_be_moved_by_non_user_profile_with_confirmation(): void
    {
        $user = User::factory()->create(['profile' => UserProfile::ADMIN]);
        $deal = $this->makeDeal($user, DealStatus::CANCELLED);

        $this->actingAs($user);

        Livewire::test(DealsKanban::class)
            ->call('moveDeal', $deal->id, DealStatus::NEGOTIATING->value)
            ->assertActionMounted('change_deal_status')
            ->callMountedAction();

        $this->assertEquals(DealStatus::NEGOTIATING, $deal->fresh()->status);
    }

    // ListDeals::promoteFromPendingToNegotiating() é a lógica reaproveitada pelo
    // afterCreate das actions "Contato" e "Solicitar Desconto" (Tabela e Kanban usam a
    // mesma getCustomViewAction()). É private, então testamos via Reflection — a
    // integração de UI (form -> afterCreate) já foi validada manualmente no navegador.
    private function callPromoteFromPendingToNegotiating(Deal $deal): void
    {
        $method = new \ReflectionMethod(\App\Filament\Resources\Deals\Pages\ListDeals::class, 'promoteFromPendingToNegotiating');
        $method->setAccessible(true);

        $fakeLivewire = new class
        {
            public array $dispatched = [];

            public function dispatch(...$args): void
            {
                $this->dispatched[] = $args;
            }
        };

        $method->invoke(null, $deal, $fakeLivewire);
    }

    public function test_pending_deal_is_promoted_to_negotiating_after_note_or_discount_request(): void
    {
        $user = User::factory()->create();
        $deal = $this->makeDeal($user, DealStatus::PENDING);

        $this->callPromoteFromPendingToNegotiating($deal);

        $this->assertEquals(DealStatus::NEGOTIATING, $deal->fresh()->status);
    }

    public function test_non_pending_deal_is_not_affected_by_promote_helper(): void
    {
        $user = User::factory()->create();
        $deal = $this->makeDeal($user, DealStatus::NEGOTIATING);

        $this->callPromoteFromPendingToNegotiating($deal);

        // Continua Negociação (a chamada é um no-op fora do status Pendente).
        $this->assertEquals(DealStatus::NEGOTIATING, $deal->fresh()->status);
    }
}
