<?php

namespace App\Observers;

use App\Models\Deal;
use App\Traits\NotificationResolveTrait;
use Illuminate\Support\Facades\Auth;

class DealObserver
{
    use NotificationResolveTrait;
    public function creating(Deal $deal): void
    {
        if (empty($deal->created_by) && Auth::check()) {
            $deal->created_by = Auth::id();
        }
    }

    public function saving(Deal $deal): void
    {
        if ($deal->status === \App\Enums\DealStatus::WON) {
            if (! $deal->actual_close_date) {
                $deal->actual_close_date = now();
            }
        } else {
            $deal->actual_close_date = null;
        }
    }

    public function created(Deal $deal): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';
        
        $title = "Novo Negócio Criado";
        $body = "{$actorName} criou o negócio '{$deal->title}'.";

        $this->dispatchNotification($deal->user_id, $title, $body, 'create');
    }

    public function updated(Deal $deal): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Negócio Atualizado";
        $body = "{$actorName} atualizou o negócio '{$deal->title}'.";

        $this->dispatchNotification($deal->user_id, $title, $body, 'update');
    }

    public function deleted(Deal $deal): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Negócio Excluída";
        $body = "{$actorName} excluiu o negócio '{$deal->title}'.";

        $this->dispatchNotification($deal->user_id, $title, $body, 'delete');
    }
}
