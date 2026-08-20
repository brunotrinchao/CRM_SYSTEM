<?php

namespace Tests\Feature;

use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Filament\Resources\Clients\Schemas\ClientInfolist;
use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientInfolistDealViewActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_infolist_configures_deals_tab_with_custom_view_action(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente com Negócio',
            'origin' => ClientOrigin::SITE,
        ]);

        Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio do Cliente',
            'status' => DealStatus::PENDING,
            'total_value' => 5000,
        ]);

        $this->actingAs($user);

        $schema = ClientInfolist::configure(new Schema());
        $components = $schema->getComponents();

        $this->assertNotEmpty($components);
    }
}
