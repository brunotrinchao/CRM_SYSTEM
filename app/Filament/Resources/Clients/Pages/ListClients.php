<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use App\Filament\Widgets\FunnelStats;
use App\Models\Client;
use App\Services\ClientService;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
             SimpleActions::getWizardCreateModal(
                width: Width::ExtraLarge,
                steps: ClientForm::getSteps(),
                actionCallback: fn (array $data) => ClientService::create($data),
                recordName: 'Cliente',
                model: Client::class,
                modal: false,
                name: 'create_client_modal',
            )
        ];
    }
}
