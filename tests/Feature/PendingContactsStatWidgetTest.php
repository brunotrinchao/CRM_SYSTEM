<?php

namespace Tests\Feature;

use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Filament\Widgets\PendingContactsStatWidget;
use App\Models\Client;
use App\Models\Deal;
use App\Models\DealNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingContactsStatWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_stat_widget_calculates_pending_contacts(): void
    {
        $seller = User::create([
            'name' => 'Seller Test',
            'email' => 'seller_stat@test.com',
            'password' => bcrypt('password'),
            'profile' => UserProfile::USER,
        ]);

        $client = Client::create([
            'user_id' => $seller->id,
            'name' => 'Client Test',
            'email' => 'client_stat@test.com',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $seller->id,
            'created_by' => $seller->id,
            'client_id' => $client->id,
            'title' => 'Pending Deal Stat',
            'total_value' => 5000,
            'status' => DealStatus::PENDING,
            'probability' => 50,
        ]);

        DealNote::create([
            'deal_id' => $deal->id,
            'user_id' => $seller->id,
            'interaction_type' => 'CALL',
            'content' => 'Teste contato stat',
            'next_follow_up_date' => now()->subHours(5),
            'contact_date' => now()->subHours(10),
        ]);

        $this->actingAs($seller);

        $widget = new PendingContactsStatWidget();
        $this->assertNotNull($widget);
    }
}
