<?php

namespace App\Observers;

use App\Models\Product;
use App\Traits\NotificationResolveTrait;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    use NotificationResolveTrait;
    public function created(Product $product): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';
        
        $title = "Novo Produto Criado";
        $body = "{$actorName} criou o produto '{$product->name}'.";

        $this->dispatchNotification($product->user_id, $title, $body, 'create');
    }

    public function updated(Product $product): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Produto Atualizado";
        $body = "{$actorName} atualizou o produto '{$product->name}'.";

        $this->dispatchNotification($product->user_id, $title, $body, 'update');
    }

    public function deleted(Product $product): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Produto Excluída";
        $body = "{$actorName} excluiu o produto '{$product->name}'.";

        $this->dispatchNotification($product->user_id, $title, $body, 'delete');
    }
}
