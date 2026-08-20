<?php

namespace Tests\Feature;

use App\Enums\ChannelNote;
use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use App\Services\DealService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealFormNoteTabTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->client = Client::create([
            'user_id' => $this->user->id,
            'name' => 'Cliente Teste',
            'origin' => ClientOrigin::SITE,
        ]);
        $this->actingAs($this->user);
    }

    public function test_deal_service_creates_deal_and_note_when_contact_fields_are_provided(): void
    {
        $data = [
            'client_id' => $this->client->id,
            'title' => 'Negócio com Contato',
            'status' => DealStatus::PENDING->value,
            'total_value' => 1500,
            // Dados da aba Contatos
            'interaction_type' => ChannelNote::CALL->value,
            'contact_date' => '2026-08-19 14:00:00',
            'content' => 'Primeira ligação realizada com sucesso',
            'next_follow_up_date' => '2026-08-25 10:00:00',
            'next_action' => 'Enviar proposta detalhada',
        ];

        $deal = DealService::create($data);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'title' => 'Negócio com Contato',
        ]);

        $this->assertDatabaseHas('deal_notes', [
            'deal_id' => $deal->id,
            'interaction_type' => ChannelNote::CALL->value,
            'content' => 'Primeira ligação realizada com sucesso',
            'next_action' => 'Enviar proposta detalhada',
        ]);
    }

    public function test_deal_service_creates_deal_without_note_when_contact_fields_are_absent(): void
    {
        $data = [
            'client_id' => $this->client->id,
            'title' => 'Negócio sem Contato',
            'status' => DealStatus::PENDING->value,
            'total_value' => 2000,
        ];

        $deal = DealService::create($data);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'title' => 'Negócio sem Contato',
        ]);

        $this->assertDatabaseCount('deal_notes', 0);
    }

    public function test_deal_service_update_adds_note_when_contact_fields_are_provided(): void
    {
        $deal = Deal::create([
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'title' => 'Negócio Original',
            'status' => DealStatus::PENDING,
            'total_value' => 1000,
        ]);

        $updateData = [
            'title' => 'Negócio Atualizado',
            'interaction_type' => ChannelNote::WHATSAPP->value,
            'content' => 'Mensagem de acompanhamento via WhatsApp',
        ];

        DealService::update($deal, $updateData);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'title' => 'Negócio Atualizado',
        ]);

        $this->assertDatabaseHas('deal_notes', [
            'deal_id' => $deal->id,
            'interaction_type' => ChannelNote::WHATSAPP->value,
            'content' => 'Mensagem de acompanhamento via WhatsApp',
        ]);
    }

    public function test_canal_is_required_outside_deal_form_and_optional_inside_deal_form(): void
    {
        $standaloneSelect = \Bjanczak\FilamentFlexFields\Filament\Forms\Components\SelectField::make('interaction_type')->required(! false);
        $dealFormSelect = \Bjanczak\FilamentFlexFields\Filament\Forms\Components\SelectField::make('interaction_type')->required(! true);

        $this->assertTrue($standaloneSelect->isRequired());
        $this->assertFalse($dealFormSelect->isRequired());

        $standaloneForm = \App\Filament\Resources\Notes\Schemas\NotesForm::getComponents(isDealForm: false);
        $dealFormComponents = \App\Filament\Resources\Notes\Schemas\NotesForm::getComponents(isDealForm: true);

        $this->assertCount(1, $standaloneForm);
        $this->assertCount(1, $dealFormComponents);
    }
}
