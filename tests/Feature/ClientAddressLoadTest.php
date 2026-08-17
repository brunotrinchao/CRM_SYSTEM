<?php

namespace Tests\Feature;

use App\Filament\Actions\SimpleActions;
use App\Models\Address;
use App\Models\Client;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAddressLoadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'profile' => 'ADMIN',
        ]);

        $this->client = Client::create([
            'user_id' => $this->user->id,
            'name' => 'Cliente Teste',
            'origin' => 'OTHER',
        ]);

        Address::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'street' => 'Av. Teste',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'Belo Horizonte',
            'state' => 'MG',
            'zip_code' => '30130-140',
            'type' => 'RESIDENCE',
        ]);
    }

    public function test_client_loads_addresses_relationship(): void
    {
        $this->actingAs($this->user);

        $loaded = $this->client->load(['addresses'])->toArray();

        $this->assertNotEmpty($loaded['addresses'], 'Addresses relacionamento deve ser populado.');
        $this->assertSame('Av. Teste', $loaded['addresses'][0]['street'] ?? null);
        $this->assertSame('30130-140', $loaded['addresses'][0]['zip_code'] ?? null);
    }

    public function test_view_modal_infolist_loads_addresses_via_relations(): void
    {
        $this->actingAs($this->user);

        $action = SimpleActions::getViewWithEditAndDelete(
            width: Width::Large,
            schemaCallback: fn ($schema) => \App\Filament\Resources\Clients\Schemas\ClientForm::configure($schema),
            schemaViewCallback: fn (Schema $schema) => \App\Filament\Resources\Clients\Schemas\ClientInfolist::configure($schema),
            actionCallback: fn ($record, $data) => null,
            model: Client::class,
            recordName: 'Cliente',
            modal: false,
            relations: ['addresses'],
        );

        $this->assertInstanceOf(ViewAction::class, $action);

        $record = Client::find($this->client->id);
        $loaded = $record->load(['addresses'])->toArray();

        $this->assertArrayHasKey('addresses', $loaded);
        $this->assertNotEmpty($loaded['addresses']);
        $this->assertSame('Av. Teste', $loaded['addresses'][0]['street']);

        $this->assertArrayHasKey('full_address', $loaded['addresses'][0]);
        $this->assertSame(
            'Av. Teste, nº 123, Centro, Belo Horizonte, MG, 30130-140',
            $loaded['addresses'][0]['full_address'],
        );
    }

    public function test_client_infolist_tabs_and_relations(): void
    {
        $this->actingAs($this->user);

        \App\Models\Deal::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'title' => 'Proposta Inicial',
            'status' => 'PENDING',
            'total_value' => 1500.00,
        ]);

        $record = Client::with(['addresses', 'deals.product'])->find($this->client->id);
        $data = $record->toArray();

        $this->assertArrayHasKey('addresses', $data);
        $this->assertNotEmpty($data['addresses']);
        $this->assertArrayHasKey('deals', $data);
        $this->assertNotEmpty($data['deals']);
        $this->assertSame('Proposta Inicial', $data['deals'][0]['title']);
    }
}
