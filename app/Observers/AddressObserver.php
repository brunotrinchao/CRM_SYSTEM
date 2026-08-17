<?php

namespace App\Observers;

use App\Models\Address;
use App\Traits\NotificationResolveTrait;
use Illuminate\Support\Facades\Auth;

class AddressObserver
{
    use NotificationResolveTrait;
    public function created(Address $address): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';
        
        $title = "Novo Endereço Criado";
        $body = "{$actorName} criou o endereço do cliente'{$address->client->name}'.";

        $this->dispatchNotification($address->user_id, $title, $body, 'create');
    }

    public function updated(Address $address): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Endereço Atualizado";
        $body = "{$actorName} atualizou o endereço para o cliente'{$address->client->name}'.";

        $this->dispatchNotification($address->user_id, $title, $body, 'update');
    }

    public function deleted(Address $address): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Endereço Excluída";
        $body = "{$actorName} excluiu um endereço do cliente'{$address->client->name}'.";

        $this->dispatchNotification($address->user_id, $title, $body, 'delete');
    }
}
