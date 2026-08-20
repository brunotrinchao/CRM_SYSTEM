<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Services\UserService;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SimpleActions::getCreateModal(
                width: Width::Large,
                schemaCallback: fn ($schema) => UserForm::configure($schema),
                actionCallback: fn (array $data) => UserService::create($data),
                recordName: 'Usuário',
                model: User::class,
                modal: false,
                name: 'create_user_modal',
            ),
        ];
    }
}
