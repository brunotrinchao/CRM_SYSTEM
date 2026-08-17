<?php

namespace App\Filament\Resources\Deals\Pages;

use App\Enums\UserProfile;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Deals\DealResource;
use App\Filament\Resources\Deals\Schemas\DealForm;
use App\Filament\Resources\Deals\Schemas\DealInfolist;
use App\Models\Deal;
use App\Services\DealService;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Override;

class ViewDeal extends ViewRecord
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Dispara automaticamente a action que abre o modal/slide-over ao entrar na rota
        $this->mountAction('custom_view');
    }

    protected function getActions(): array
    {
        return [
            SimpleActions::getViewWithEditAndDelete(
                width: Width::Large,
                schemaCallback: fn($schema) => DealForm::configure($schema),
                schemaViewCallback: fn(Schema $schema) => DealInfolist::configure($schema),
                actionCallback: fn(Model $record, array $data) => DealService::update($record, $data),
                model: Deal::class,
                recordName: 'Negócio',
                modal: false,
                relations: ['client', 'products', 'discountRequests']
            ),
        ];
    }

    #[Override]
    public static function canAccess(array $parameters = []): bool
    {
        // Primeiro valida a regra padrão do Filament/Pai
        if (!parent::canAccess($parameters)) {
            return false;
        }

        $user = Auth::user();

        // Se não houver usuário logado, nega o acesso
        if (!$user) {
            return false;
        }

        // Bloqueia o acesso se o perfil do usuário for USER
        // (Ajuste a propriedade/método do perfil conforme o seu Model User)
        if ($user->profile === UserProfile::USER->value || $user->profile === 'user') {
            return false;
        }

        return true;
    }
}
