<?php

namespace App\Observers;

use App\Enums\DiscountRequestStatus;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Deals\DealResource;
use App\Filament\Resources\Deals\Schemas\DealForm;
use App\Filament\Resources\Deals\Schemas\DealInfolist;
use App\Models\Deal;
use App\Models\DiscountRequest;
use App\Services\DealService;
use App\Traits\NotificationResolveTrait;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DiscountRequestObserver
{
    use NotificationResolveTrait;
    public function created(DiscountRequest $discount): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';
        
        $title = "Novo Desconto Solicitado";
        $body = "{$actorName} solicitou desconto a '{$discount->reviewer?->name}'.";

        $action =  Action::make('view')
            ->icon(Phosphor::ArrowSquareUpRightLight)
            ->label('Abrir')
            ->link()
            ->url(DealResource::getUrl('view', ['record' => $discount->deal]))
            ->slideOver();

        $this->dispatchNotification($discount->requester->id, $title, $body, 'create', $action);
    }

    public function updated(DiscountRequest $discount): void
    {
        $actor = Auth::user();
    $actorName = $actor ? $actor->name : 'Sistema';
    $status = $discount->status;

    [$title, $body] = match ($status) {
        DiscountRequestStatus::APPROVED => [
            "Solicitação de Desconto Aprovada",
            "{$actorName} aprovou a solicitação de desconto."
        ],
        DiscountRequestStatus::REJECTED => [
            "Solicitação de Desconto Recusada",
            "{$actorName} recusou a solicitação de desconto."
        ],
        default => [
            "Solicitação de Desconto Atualizada",
            "{$actorName} atualizou a solicitação de desconto."
        ],
    };

    $this->dispatchDiscountNotification($discount->requester->id, $title, $body, $status);
    }

    public function deleted(DiscountRequest $discount): void
    {
        $actor = Auth::user();
        $actorName = $actor ? $actor->name : 'Sistema';

        $title = "Desconto Solicitado Excluído";
        $body = "{$actorName} excluiu o desconto solicitado.";

        $this->dispatchNotification($discount->requester->id, $title, $body, 'delete');
    }
}
