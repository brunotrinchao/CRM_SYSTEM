<?php

namespace App\Observers;

use App\Models\Category;
use App\Traits\NotificationResolveTrait;
use Illuminate\Support\Facades\Auth;

class CategoryObserver
{
    use NotificationResolveTrait;
    public function created(Category $category): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';
        
        $title = "Nova Categoria Criada";
        $body = "{$actorName} criou a categoria '{$category->name}'.";

        $this->dispatchNotification($category->user_id, $title, $body, 'create');
    }

    public function updated(Category $category): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Categoria Atualizada";
        $body = "{$actorName} atualizou a categoria '{$category->name}'.";

        $this->dispatchNotification($category->user_id, $title, $body, 'update');
    }

    public function deleted(Category $category): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Categoria Excluída";
        $body = "{$actorName} excluiu a categoria '{$category->name}'.";

        $this->dispatchNotification($category->user_id, $title, $body, 'delete');
    }

}
