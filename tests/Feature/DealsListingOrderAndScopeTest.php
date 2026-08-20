<?php

namespace Tests\Feature;

use App\Enums\ClientOrigin;
use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Filament\Resources\Deals\DealResource;
use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealsListingOrderAndScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_deals_are_ordered_by_newest_created_first(): void
    {
        $admin = User::factory()->create(['profile' => UserProfile::ADMIN]);
        $client = Client::create([
            'user_id' => $admin->id,
            'name' => 'Cliente Teste',
            'origin' => ClientOrigin::SITE,
        ]);

        $dealOld = Deal::create([
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'title' => 'Negócio Antigo',
            'status' => DealStatus::PENDING,
            'total_value' => 1000,
        ]);
        $dealOld->created_at = now()->subDays(5);
        $dealOld->save();

        $dealNew = Deal::create([
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'title' => 'Negócio Novo',
            'status' => DealStatus::PENDING,
            'total_value' => 2000,
            'created_at' => now(),
        ]);

        $this->actingAs($admin);

        $results = DealResource::getEloquentQuery()->get();
        // dd(DealResource::getEloquentQuery()->toSql(), $results->pluck('id', 'title'));

        $this->assertEquals($dealNew->id, $results->first()->id);
        $this->assertEquals($dealOld->id, $results->last()->id);
    }

    public function test_user_profile_only_sees_deals_assigned_to_them(): void
    {
        $seller1 = User::factory()->create(['profile' => UserProfile::USER]);
        $seller2 = User::factory()->create(['profile' => UserProfile::USER]);

        $client = Client::create([
            'user_id' => $seller1->id,
            'name' => 'Cliente Teste',
            'origin' => ClientOrigin::SITE,
        ]);

        $deal1 = Deal::create([
            'user_id' => $seller1->id,
            'client_id' => $client->id,
            'title' => 'Negócio do Vendedor 1',
            'status' => DealStatus::PENDING,
            'total_value' => 1000,
        ]);

        $deal2 = Deal::create([
            'user_id' => $seller2->id,
            'client_id' => $client->id,
            'title' => 'Negócio do Vendedor 2',
            'status' => DealStatus::PENDING,
            'total_value' => 2000,
        ]);

        // Autenticado como Vendedor 1
        $this->actingAs($seller1);
        $results = DealResource::getEloquentQuery()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($deal1->id, $results->first()->id);

        // Autenticado como Vendedor 2
        $this->actingAs($seller2);
        $resultsSeller2 = DealResource::getEloquentQuery()->get();

        $this->assertCount(1, $resultsSeller2);
        $this->assertEquals($deal2->id, $resultsSeller2->first()->id);
    }

    public function test_admin_profile_sees_all_deals(): void
    {
        $admin = User::factory()->create(['profile' => UserProfile::ADMIN]);
        $seller = User::factory()->create(['profile' => UserProfile::USER]);

        $client = Client::create([
            'user_id' => $admin->id,
            'name' => 'Cliente Teste',
            'origin' => ClientOrigin::SITE,
        ]);

        Deal::create([
            'user_id' => $seller->id,
            'client_id' => $client->id,
            'title' => 'Negócio do Vendedor',
            'status' => DealStatus::PENDING,
            'total_value' => 1000,
        ]);

        Deal::create([
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'title' => 'Negócio do Admin',
            'status' => DealStatus::PENDING,
            'total_value' => 2000,
        ]);

        $this->actingAs($admin);
        $results = DealResource::getEloquentQuery()->get();

        $this->assertCount(2, $results);
    }
}
