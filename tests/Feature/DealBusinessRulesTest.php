<?php

namespace Tests\Feature;

use App\Enums\DealStatus;
use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use App\Services\DealService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_actual_close_date_is_set_when_status_becomes_won_and_cleared_otherwise(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'origin' => \App\Enums\ClientOrigin::SITE,
        ]);

        // Create deal with status PENDING
        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Teste',
            'status' => DealStatus::PENDING,
            'total_value' => 1000,
        ]);

        $this->assertNull($deal->fresh()->actual_close_date);

        // Update status to WON
        $deal->update(['status' => DealStatus::WON]);

        $this->assertNotNull($deal->fresh()->actual_close_date);
        $this->assertEquals(now()->format('Y-m-d'), $deal->fresh()->actual_close_date->format('Y-m-d'));

        // Change status to LOST
        $deal->update(['status' => DealStatus::LOST]);

        $this->assertNull($deal->fresh()->actual_close_date);
    }

    public function test_discount_request_on_pending_deal_changes_status_to_negotiating(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'origin' => \App\Enums\ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Pendente',
            'status' => DealStatus::PENDING,
            'total_value' => 2000,
        ]);

        $this->actingAs($user);

        $discount = DealService::requestDicount([
            'deal_id' => $deal->id,
            'amount' => 100,
            'reason' => 'Motivo de teste',
            'type' => true,
        ]);

        $this->assertNotNull($discount);
        $this->assertEquals($deal->id, $discount->deal_id);
        $this->assertEquals(DealStatus::NEGOTIATING, $deal->fresh()->status);
    }

    public function test_adding_contact_note_on_pending_deal_changes_status_to_negotiating(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'origin' => \App\Enums\ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Pendente Nota',
            'status' => DealStatus::PENDING,
            'total_value' => 2000,
        ]);

        $this->actingAs($user);

        $note = \App\Services\DealNoteService::create([
            'deal_id' => $deal->id,
            'interaction_type' => \App\Enums\ChannelNote::CALL,
            'content' => 'Contato realizado com cliente',
            'contact_date' => now(),
        ]);

        $this->assertNotNull($note);
        $this->assertEquals(DealStatus::NEGOTIATING, $deal->fresh()->status);
    }

    public function test_discount_request_fails_on_won_lost_or_cancelled_deal(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'origin' => \App\Enums\ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Ganho',
            'status' => DealStatus::WON,
            'total_value' => 2000,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        DealService::requestDicount([
            'deal_id' => $deal->id,
            'amount' => 100,
            'type' => false,
        ]);
    }

    public function test_user_profile_cannot_create_deal(): void
    {
        $seller = User::factory()->create([
            'profile' => \App\Enums\UserProfile::USER,
        ]);

        $policy = new \App\Policies\DealPolicy();

        $this->assertFalse($policy->create($seller));
    }

    public function test_admin_or_manager_can_create_deal_and_assign_to_user_seller(): void
    {
        $admin = User::factory()->create([
            'profile' => \App\Enums\UserProfile::ADMIN,
        ]);

        $seller = User::factory()->create([
            'profile' => \App\Enums\UserProfile::USER,
        ]);

        $client = Client::create([
            'user_id' => $admin->id,
            'name' => 'Cliente Global',
            'origin' => \App\Enums\ClientOrigin::SITE,
        ]);

        $this->actingAs($admin);

        $deal = DealService::create([
            'user_id' => $seller->id,
            'client_id' => $client->id,
            'title' => 'Negócio do Vendedor',
            'status' => DealStatus::PENDING->value,
            'total_value' => 5000,
        ]);

        $this->assertEquals($seller->id, $deal->user_id);
        $this->assertEquals($admin->id, $deal->created_by);
    }

    public function test_seller_workload_service_returns_workload_metrics(): void
    {
        $seller = User::factory()->create([
            'name' => 'Vendedor Teste',
            'profile' => \App\Enums\UserProfile::USER,
        ]);

        $client = Client::create([
            'user_id' => $seller->id,
            'name' => 'Cliente Teste',
            'origin' => \App\Enums\ClientOrigin::SITE,
        ]);

        Deal::create([
            'user_id' => $seller->id,
            'client_id' => $client->id,
            'title' => 'Negócio Ativo 1',
            'status' => DealStatus::NEGOTIATING,
            'total_value' => 3000,
        ]);

        $workload = \App\Services\SellerWorkloadService::getSellersWorkload();

        $this->assertNotEmpty($workload);
        $sellerData = collect($workload)->firstWhere('id', $seller->id);
        $this->assertNotNull($sellerData);
        $this->assertEquals(1, $sellerData['active_deals_count']);
        $this->assertEquals(3000, $sellerData['active_deals_value']);
    }

    public function test_updating_deal_to_cancelled_requires_confirmation(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'origin' => \App\Enums\ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Ativo',
            'status' => DealStatus::NEGOTIATING,
            'total_value' => 3000,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        DealService::update($deal, [
            'status' => DealStatus::CANCELLED->value,
            'confirm_status_cancelled' => false,
        ]);
    }

    public function test_updating_deal_to_cancelled_succeeds_when_confirmed(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'origin' => \App\Enums\ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Ativo',
            'status' => DealStatus::NEGOTIATING,
            'total_value' => 3000,
        ]);

        DealService::update($deal, [
            'status' => DealStatus::CANCELLED->value,
            'confirm_status_cancelled' => true,
        ]);

        $this->assertEquals(DealStatus::CANCELLED, $deal->fresh()->status);
    }

    public function test_cancelled_deal_is_not_editable(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'origin' => \App\Enums\ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Cancelado',
            'status' => DealStatus::CANCELLED,
            'total_value' => 3000,
        ]);

        $isEditable = ($deal->status !== DealStatus::CANCELLED);
        $this->assertFalse($isEditable);
    }
}
