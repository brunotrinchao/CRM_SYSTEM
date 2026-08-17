<?php

namespace App\Observers;

use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Clients\Schemas\ClientInfolist;
use App\Models\Client;
use App\Traits\NotificationResolveTrait;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;

class ClientObserver
{

    use NotificationResolveTrait;
    public function created(Client $client): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';
        
        $title = "Novo Cliente Criado";
        $body = "{$actorName} criou o cliente '{$client->name}'.";

        $url = route('filament.admin.resources.clients.edit', ['record' => $client]);

        $this->dispatchNotification($client->user_id, $title, $body, 'create');
    }

    public function updated(Client $client): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Cliente Atualizado";
        $body = "{$actorName} atualizou o cliente '{$client->name}'.";

        $this->dispatchNotification($client->user_id, $title, $body, 'update');
    }

    public function deleted(Client $client): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Cliente Excluída";
        $body = "{$actorName} excluiu o cliente '{$client->name}'.";

        $this->dispatchNotification($client->user_id, $title, $body, 'delete');
    }
}
