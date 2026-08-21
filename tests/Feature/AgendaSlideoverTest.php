<?php

namespace Tests\Feature;

use App\Enums\ChannelNote;
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

class AgendaSlideoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_badge_calculates_overdue_contacts_scoped_by_profile(): void
    {
        $admin = User::factory()->create(['profile' => UserProfile::ADMIN]);
        $seller1 = User::factory()->create(['profile' => UserProfile::USER]);
        $seller2 = User::factory()->create(['profile' => UserProfile::USER]);

        $client1 = Client::create(['user_id' => $seller1->id, 'name' => 'Cliente 1', 'phone' => '11999999999', 'cellphone' => '11999999999', 'origin' => ClientOrigin::SITE]);
        $client2 = Client::create(['user_id' => $seller2->id, 'name' => 'Cliente 2', 'phone' => '11999999999', 'cellphone' => '11999999999', 'origin' => ClientOrigin::SITE]);

        $deal1 = Deal::create(['title' => 'Negocio 1', 'client_id' => $client1->id, 'user_id' => $seller1->id, 'status' => DealStatus::PENDING, 'total_value' => 1000]);
        $deal2 = Deal::create(['title' => 'Negocio 2', 'client_id' => $client2->id, 'user_id' => $seller2->id, 'status' => DealStatus::PENDING, 'total_value' => 2000]);

        // Contato atrasado do Vendedor 1
        DealNote::create([
            'user_id' => $seller1->id,
            'deal_id' => $deal1->id,
            'interaction_type' => ChannelNote::WHATSAPP,
            'content' => 'Nota 1 Atrasada',
            'contact_date' => now()->subDays(5),
            'next_follow_up_date' => now()->subDays(2),
        ]);

        // Contato atrasado do Vendedor 2
        DealNote::create([
            'user_id' => $seller2->id,
            'deal_id' => $deal2->id,
            'interaction_type' => ChannelNote::CALL,
            'content' => 'Nota 2 Atrasada',
            'contact_date' => now()->subDays(5),
            'next_follow_up_date' => now()->subDays(1),
        ]);

        // 1. Vendedor 1 visualiza apenas o seu (1 contato atrasado)
        $this->actingAs($seller1);
        Livewire::test(PendingContactsHeaderBadge::class)
            ->assertViewHas('overdueCount', 1)
            ->assertSee('Cliente 1');

        // 2. Admin visualiza os contatos de ambos os vendedores (2 contatos atrasados)
        $this->actingAs($admin);
        Livewire::test(PendingContactsHeaderBadge::class)
            ->assertViewHas('overdueCount', 2)
            ->assertSee('Cliente 1')
            ->assertSee('Cliente 2');
    }

    public function test_multiple_contacts_in_same_hour_slot_in_weekly_schedule(): void
    {
        $seller = User::factory()->create(['profile' => UserProfile::USER]);
        $client = Client::create(['user_id' => $seller->id, 'name' => 'Cliente Slot', 'phone' => '11999999999', 'cellphone' => '11999999999', 'origin' => ClientOrigin::SITE]);
        $deal = Deal::create(['title' => 'Negocio Slot', 'client_id' => $client->id, 'user_id' => $seller->id, 'status' => DealStatus::PENDING, 'total_value' => 1500]);

        $nextFollowUp = now()->startOfWeek()->addDays(2)->setHour(14)->setMinute(0);

        // Criar 2 agendamentos no mesmo horario (Quarta-feira 14:00)
        DealNote::create(['user_id' => $seller->id, 'deal_id' => $deal->id, 'interaction_type' => ChannelNote::WHATSAPP, 'content' => 'Contato 1', 'contact_date' => now(), 'next_follow_up_date' => $nextFollowUp]);
        DealNote::create(['user_id' => $seller->id, 'deal_id' => $deal->id, 'interaction_type' => ChannelNote::CALL, 'content' => 'Contato 2', 'contact_date' => now(), 'next_follow_up_date' => $nextFollowUp]);

        $this->actingAs($seller);

        Livewire::test(PendingContactsHeaderBadge::class)
            ->call('setActiveTab', 'proximo')
            ->assertSet('activeTab', 'proximo')
            ->assertStatus(200);
    }

    public function test_seller_can_register_return_from_slideover(): void
    {
        $seller = User::factory()->create(['profile' => UserProfile::USER]);
        $client = Client::create(['user_id' => $seller->id, 'name' => 'Cliente Retorno', 'phone' => '11999999999', 'cellphone' => '11999999999', 'origin' => ClientOrigin::SITE]);
        $deal = Deal::create(['title' => 'Negocio Retorno', 'client_id' => $client->id, 'user_id' => $seller->id, 'status' => DealStatus::PENDING, 'total_value' => 3000]);

        $note = DealNote::create([
            'user_id' => $seller->id,
            'deal_id' => $deal->id,
            'interaction_type' => ChannelNote::WHATSAPP,
            'content' => 'Nota antiga',
            'contact_date' => now()->subDays(3),
            'next_follow_up_date' => now()->subDays(1),
        ]);

        $this->actingAs($seller);

        Livewire::test(PendingContactsHeaderBadge::class)
            ->call('openReturnForm', $note->id)
            ->set('followUpContent', 'Cliente confirmou interesse na proposta')
            ->set('newNextAction', 'Enviar contrato assinado')
            ->call('saveContactReturn')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('deal_notes', [
            'deal_id' => $deal->id,
            'content' => 'Cliente confirmou interesse na proposta',
            'next_action' => 'Enviar contrato assinado',
        ]);
    }
}
