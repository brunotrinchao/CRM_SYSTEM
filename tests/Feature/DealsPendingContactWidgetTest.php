<?php

namespace Tests\Feature;

use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Filament\Widgets\DealsPendingContactWidget;
use App\Models\Client;
use App\Models\Deal;
use App\Models\DealNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealsPendingContactWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_user_sees_only_own_pending_deals(): void
    {
        $seller1 = User::create([
            'name' => 'Seller 1',
            'email' => 'seller1@test.com',
            'password' => bcrypt('password'),
            'profile' => UserProfile::USER,
        ]);

        $seller2 = User::create([
            'name' => 'Seller 2',
            'email' => 'seller2@test.com',
            'password' => bcrypt('password'),
            'profile' => UserProfile::USER,
        ]);

        $client = Client::create([
            'user_id' => $seller1->id,
            'name' => 'Client Test',
            'email' => 'client@test.com',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal1 = Deal::create([
            'user_id' => $seller1->id,
            'created_by' => $seller1->id,
            'client_id' => $client->id,
            'title' => 'Deal Seller 1',
            'total_value' => 5000,
            'status' => DealStatus::PENDING,
            'probability' => 50,
        ]);

        $deal2 = Deal::create([
            'user_id' => $seller2->id,
            'created_by' => $seller2->id,
            'client_id' => $client->id,
            'title' => 'Deal Seller 2',
            'total_value' => 3000,
            'status' => DealStatus::PENDING,
            'probability' => 50,
        ]);

        $this->actingAs($seller1);

        $widget = new DealsPendingContactWidget();
        $pending = $widget->getPendingDeals();

        $this->assertCount(1, $pending);
        $this->assertEquals($deal1->id, $pending->first()['deal']->id);
    }

    public function test_deal_overdue_more_than_24h_is_flagged_red(): void
    {
        $seller = User::create([
            'name' => 'Seller Test',
            'email' => 'seller@test.com',
            'password' => bcrypt('password'),
            'profile' => UserProfile::USER,
        ]);

        $client = Client::create([
            'user_id' => $seller->id,
            'name' => 'Client Test',
            'email' => 'client2@test.com',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal = Deal::create([
            'user_id' => $seller->id,
            'created_by' => $seller->id,
            'client_id' => $client->id,
            'title' => 'Overdue Deal',
            'total_value' => 5000,
            'status' => DealStatus::NEGOTIATING,
            'probability' => 50,
        ]);

        DealNote::create([
            'deal_id' => $deal->id,
            'user_id' => $seller->id,
            'interaction_type' => 'CALL',
            'content' => 'Teste contato atrasado',
            'next_follow_up_date' => now()->subHours(30),
            'contact_date' => now()->subHours(35),
        ]);

        $this->actingAs($seller);

        $widget = new DealsPendingContactWidget();
        $pending = $widget->getPendingDeals();

        $this->assertCount(1, $pending);
        $this->assertTrue($pending->first()['is_overdue_24h']);
    }
}
