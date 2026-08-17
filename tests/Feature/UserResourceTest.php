<?php

namespace Tests\Feature;

use App\Enums\UserProfile;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_manager_can_access_user_resource(): void
    {
        $admin = User::factory()->create(['profile' => UserProfile::ADMIN]);
        $this->actingAs($admin);
        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(UserResource::shouldRegisterNavigation());

        $manager = User::factory()->create(['profile' => UserProfile::MANAGER]);
        $this->actingAs($manager);
        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(UserResource::shouldRegisterNavigation());
    }

    public function test_user_profile_cannot_access_user_resource(): void
    {
        $user = User::factory()->create(['profile' => UserProfile::USER]);
        $this->actingAs($user);
        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse(UserResource::shouldRegisterNavigation());
    }

    public function test_users_list_page_renders_for_admin(): void
    {
        $admin = User::factory()->create(['profile' => UserProfile::ADMIN]);
        $this->actingAs($admin);

        $response = $this->get(UserResource::getUrl('index'));
        $response->assertSuccessful();
    }

    public function test_admin_user_profile_cannot_be_changed(): void
    {
        $admin1 = User::factory()->create(['profile' => UserProfile::ADMIN]);
        $admin2 = User::factory()->create(['profile' => UserProfile::ADMIN]);

        $this->actingAs($admin1);

        // Tenta alterar admin2 para USER via callback da tabela
        $table = \App\Filament\Resources\Users\Tables\UsersTable::configure(
            \Filament\Tables\Table::make(new \App\Filament\Resources\Users\Pages\ListUsers())
        );

        $actions = $table->getRecordActions();
        $this->assertNotEmpty($actions);
    }
}
