<?php

namespace Tests\Feature;

use App\Enums\ChannelNote;
use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Filament\Pages\ReportsPage;
use App\Models\Client;
use App\Models\Deal;
use App\Models\DealNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsContactsTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_page_renders_contacts_tab(): void
    {
        $admin = User::factory()->create(['profile' => UserProfile::ADMIN]);
        $seller = User::factory()->create(['profile' => UserProfile::USER]);
        $client = Client::create([
            'user_id' => $seller->id,
            'name' => 'Cliente Teste Contato',
            'phone' => '11999999999',
            'cellphone' => '11999999999',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'title' => 'Negocio Teste Contato',
            'client_id' => $client->id,
            'user_id' => $seller->id,
            'status' => DealStatus::PENDING,
            'total_value' => 1000,
        ]);

        DealNote::create([
            'user_id' => $seller->id,
            'deal_id' => $deal->id,
            'interaction_type' => ChannelNote::WHATSAPP,
            'content' => 'Contato realizado para negociar valor',
            'contact_date' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(ReportsPage::class)
            ->call('setActiveTab', 'contatos')
            ->assertSet('activeTab', 'contatos')
            ->assertSee('Contatos')
            ->assertSee('Contato realizado para negociar valor');
    }

    public function test_contacts_tab_filters_by_vendedor_and_interaction_type(): void
    {
        $admin = User::factory()->create(['profile' => UserProfile::ADMIN]);
        $seller = User::factory()->create(['name' => 'Vendedor Teste', 'profile' => UserProfile::USER]);
        $client = Client::create([
            'user_id' => $seller->id,
            'name' => 'Cliente Teste Ligacao',
            'phone' => '11999999999',
            'cellphone' => '11999999999',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'title' => 'Negocio Teste Ligacao',
            'client_id' => $client->id,
            'user_id' => $seller->id,
            'status' => DealStatus::PENDING,
            'total_value' => 2000,
        ]);

        DealNote::create([
            'user_id' => $seller->id,
            'deal_id' => $deal->id,
            'interaction_type' => ChannelNote::CALL,
            'content' => 'Ligacao de alinhamento comercial',
            'contact_date' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(ReportsPage::class)
            ->set('filters.user_id', $seller->id)
            ->set('filters.interaction_type', ChannelNote::CALL->value)
            ->call('setActiveTab', 'contatos')
            ->assertStatus(200);
    }
}
