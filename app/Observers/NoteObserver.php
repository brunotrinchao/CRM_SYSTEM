<?php

namespace App\Observers;

use App\Models\DealNote as Note;
use App\Traits\NotificationResolveTrait;
use Illuminate\Support\Facades\Auth;

class NoteObserver
{
    use NotificationResolveTrait;
    public function created(Note $note): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';
        
        $title = "Nova Nota Criado";
        $body = "{$actorName} criou uma nota '{$note->name}' para o negócio '{$note->deal->title}'.";

        $this->dispatchNotification($note->user_id, $title, $body, 'create');
    }

    public function updated(Note $note): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Nota Atualizada";
        $body = "{$actorName} atualizou a nota '{$note->name}' do negócio '{$note->deal->title}'.";

        $this->dispatchNotification($note->user_id, $title, $body, 'update');
    }

    public function deleted(Note $note): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Nota Excluída";
        $body = "{$actorName} excluiu a nota '{$note->name}' do negócio '{$note->deal->name}'.";

        $this->dispatchNotification($note->user_id, $title, $body, 'delete');
    }
}
