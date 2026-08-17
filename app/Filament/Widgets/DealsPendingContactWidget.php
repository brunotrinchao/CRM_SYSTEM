<?php

namespace App\Filament\Widgets;

use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Deals\Schemas\DealForm;
use App\Filament\Resources\Deals\Schemas\DealInfolist;
use App\Filament\Resources\Notes\Schemas\NotesForm;
use App\Models\Deal;
use App\Models\DealNote;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DealsPendingContactWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.widgets.deals-pending-contact-widget';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = 1;

    public function getPendingDeals()
    {
        $user = Auth::user();

        $query = Deal::query()
            ->whereIn('status', [DealStatus::PENDING, DealStatus::NEGOTIATING])
            ->with(['client', 'user', 'notesList' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }]);

        if ($user?->profile === UserProfile::USER) {
            $query->where('user_id', $user->id);
        } else {
            // Para ADMIN e MANAGER: busca negócios de todos os usuários com perfil USER
            $query->whereHas('user', function ($q) {
                $q->where('profile', UserProfile::USER);
            });
        }

        $now = now();

        return $query->get()->map(function ($deal) use ($now) {
            $latestNote = $deal->notesList->first();
            $nextContact = $latestNote?->next_follow_up_date;

            $isPending = false;
            $isOverdue24h = false;
            $hoursDiff = 0;
            $nextContactHuman = 'Sem agendamento';

            if (! $nextContact) {
                $isPending = true;
                $nextContactHuman = 'Sem data prevista';
            } else {
                $nextContactHuman = $nextContact->format('d/m/Y H:i');
                if ($nextContact->isPast() || $nextContact->isToday()) {
                    $isPending = true;
                    if ($nextContact->isPast()) {
                        $hoursDiff = (int) $nextContact->diffInHours($now);
                        if ($hoursDiff >= 24) {
                            $isOverdue24h = true;
                        }
                    }
                }
            }

            return [
                'deal' => $deal,
                'client_name' => $deal->client?->name ?? 'Sem Cliente',
                'seller_name' => $deal->user?->name ?? 'Sem Vendedor',
                'latest_note' => $latestNote?->content,
                'next_contact' => $nextContact,
                'next_contact_human' => $nextContactHuman,
                'is_pending' => $isPending,
                'is_overdue_24h' => $isOverdue24h,
                'hours_diff' => $hoursDiff,
            ];
        })
        ->filter(fn ($item) => $item['is_pending'])
        ->sortBy([
            ['is_overdue_24h', 'desc'],
            ['hours_diff', 'desc'],
        ])
        ->values();
    }

    public function viewDealAction(): Action
    {
        return SimpleActions::getViewWithEditAndDelete(
            width: Width::Large,
            schemaCallback: fn ($schema) => DealForm::configure($schema),
            actionCallback: function (Deal $record, array $data) {
                $record->update($data);
            },
            model: Deal::class,
            schemaViewCallback: fn ($schema) => DealInfolist::configure($schema),
            recordName: 'Negócio',
            modal: false,
            deleteAction: fn (Deal $record): bool => $record->canBeDeleted(),
        )
        ->record(fn (array $arguments): ?Deal => isset($arguments['record']) ? Deal::find($arguments['record']) : null);
    }

    public function addNoteAction(): Action
    {
        return Action::make('addNote')
            ->label('Registrar Contato')
            ->modalHeading('Registrar Novo Contato')
            ->modalWidth(Width::Medium)
            ->slideOver()
            ->schema(fn ($schema) => NotesForm::configure($schema))
            ->action(function (array $data, array $arguments) {
                $dealId = $arguments['deal_id'] ?? null;
                if (! $dealId) {
                    return;
                }

                $deal = Deal::find($dealId);
                if (! $deal) {
                    return;
                }

                DealNote::create([
                    'deal_id' => $deal->id,
                    'user_id' => Auth::id(),
                    'interaction_type' => $data['interaction_type'] ?? 'CALL',
                    'content' => $data['content'] ?? '',
                    'contact_date' => $data['contact_date'] ?? now(),
                    'next_follow_up_date' => $data['next_follow_up_date'],
                    'next_action' => $data['next_action'] ?? null,
                ]);

                $deal->update([
                    'last_contact_date' => now(),
                ]);

                Notification::make()
                    ->title('Contato registrado com sucesso')
                    ->success()
                    ->send();
            });
    }
}
