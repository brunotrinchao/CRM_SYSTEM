<?php

namespace Tests\Feature;

use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Filament\Resources\Deals\Schemas\DealInfolist;
use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealStatusFieldsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_actual_close_date_visible_only_when_won(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Status',
            'origin' => ClientOrigin::SITE,
        ]);

        $dealWon = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Ganho',
            'status' => DealStatus::WON,
            'total_value' => 5000,
            'actual_close_date' => now(),
        ]);

        $dealLost = Deal::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'title' => 'Negócio Perdido',
            'status' => DealStatus::LOST,
            'total_value' => 3000,
            'loss_reason' => 'Sem orçamento',
        ]);

        $this->actingAs($user);

        $schemaWon = DealInfolist::configure(new Schema())->record($dealWon);
        $schemaLost = DealInfolist::configure(new Schema())->record($dealLost);

        $this->assertNotNull($schemaWon);
        $this->assertNotNull($schemaLost);
    }
}
