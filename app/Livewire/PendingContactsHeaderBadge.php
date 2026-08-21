<?php

namespace App\Livewire;

use App\Enums\ChannelNote;
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
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PendingContactsHeaderBadge extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public string $activeTab = 'atrasados';
    public int $weekOffset = 0;
    public bool $isFullWidth = false;

    // Estado do Formulário de Registro de Retorno
    public ?int $selectedNoteId = null;
    public ?string $followUpContent = null;
    public ?string $newNextFollowUpDate = null;
    public ?string $newNextAction = null;
    public string $interactionType = 'WHATSAPP';
    public bool $showReturnForm = false;

    // Estado para Detalhes de Horário com Múltiplos Contatos
    public ?string $selectedSlotKey = null;
    public array $slotContacts = [];

    protected $listeners = ['refreshAgenda' => '$refresh'];

    public function openAgenda(): void
    {
        $this->activeTab = 'atrasados';
        $this->weekOffset = 0;
        $this->isFullWidth = false;
        $this->showReturnForm = false;
        $this->selectedSlotKey = null;
        $this->selectedNoteId = null;
        $this->dispatch('open-modal', id: 'agenda-slideover');
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->showReturnForm = false;
        $this->selectedSlotKey = null;
        if ($tab === 'proximo') {
            $this->weekOffset = 0;
        }
    }

    public function toggleFullWidth(): void
    {
        $this->isFullWidth = ! $this->isFullWidth;
    }

    public function navigateWeek(int $direction): void
    {
        $this->weekOffset += $direction;
        $this->selectedSlotKey = null;
    }

    public function resetWeek(): void
    {
        $this->weekOffset = 0;
        $this->selectedSlotKey = null;
    }

    public function openReturnForm(int $noteId): void
    {
        $note = DealNote::find($noteId);
        if (! $note) {
            return;
        }

        $this->selectedNoteId = $note->id;
        $this->interactionType = $note->interaction_type instanceof ChannelNote ? $note->interaction_type->value : ($note->interaction_type ?? 'WHATSAPP');
        $this->followUpContent = '';
        $this->newNextFollowUpDate = null;
        $this->newNextAction = '';
        $this->showReturnForm = true;
    }

    public function closeReturnForm(): void
    {
        $this->showReturnForm = false;
        $this->selectedNoteId = null;
    }

    public function saveContactReturn(): void
    {
        if (! $this->selectedNoteId) {
            return;
        }

        $originalNote = DealNote::with('deal')->find($this->selectedNoteId);
        if (! $originalNote) {
            return;
        }

        $user = Auth::user();

        // 1. Criar o novo registro de nota de contato
        DealNote::create([
            'user_id' => $user->id,
            'deal_id' => $originalNote->deal_id,
            'interaction_type' => $this->interactionType,
            'content' => $this->followUpContent ?: 'Retorno de acompanhamento realizado via agenda.',
            'contact_date' => now(),
            'next_follow_up_date' => $this->newNextFollowUpDate ? Carbon::parse($this->newNextFollowUpDate) : null,
            'next_action' => $this->newNextAction,
        ]);

        $this->showReturnForm = false;
        $this->selectedNoteId = null;
        $this->dispatch('refreshAgenda');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Retorno registrado com sucesso!',
        ]);
    }

    public function selectSlot(string $dayDate, int $hour): void
    {
        $slotKey = "{$dayDate}_{$hour}";
        $this->selectedSlotKey = $slotKey;

        $weeklyMatrix = $this->getWeeklyScheduleProperty();
        $this->slotContacts = $weeklyMatrix['matrix'][$dayDate][$hour] ?? [];

        if (! empty($this->slotContacts)) {
            $this->dispatch('open-modal', id: 'slot-contacts-modal');
        }
    }

    public function getOverdueContactsProperty()
    {
        $user = Auth::user();
        if (! $user) {
            return collect();
        }

        $query = Deal::query()
            ->whereIn('status', [DealStatus::PENDING, DealStatus::NEGOTIATING])
            ->with(['notesList' => fn ($q) => $q->orderBy('created_at', 'desc'), 'user', 'client']);

        if ($user->profile === UserProfile::USER) {
            $query->where('user_id', $user->id);
        } else {
            $query->whereHas('user', fn ($q) => $q->where('profile', UserProfile::USER));
        }

        $now = now();

        $pendingDeals = $query->get()->filter(function ($deal) use ($now) {
            $latestNote = $deal->notesList->first();
            $nextContact = $latestNote?->next_follow_up_date;

            if (! $nextContact) {
                return true;
            }

            return $nextContact->isPast() || $nextContact->isToday();
        });

        return $pendingDeals->map(function ($deal) {
            $latestNote = $deal->notesList->first();
            if ($latestNote) {
                return $latestNote;
            }

            $fallbackNote = new DealNote([
                'next_follow_up_date' => $deal->created_at,
                'next_action' => 'Primeiro contato pendente',
                'user_id' => $deal->user_id,
            ]);
            $fallbackNote->setRelation('deal', $deal);
            $fallbackNote->setRelation('user', $deal->user);

            return $fallbackNote;
        })->sortBy(fn ($n) => $n->next_follow_up_date)->values();
    }

    public function getWeeklyScheduleProperty(): array
    {
        $user = Auth::user();
        if (! $user) {
            return ['days' => [], 'hours' => [], 'matrix' => []];
        }

        $startOfWeek = now()->startOfWeek(Carbon::MONDAY)->addWeeks($this->weekOffset)->startOfDay();
        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $days = [];
        $currentDay = $startOfWeek->copy();
        for ($i = 0; $i < 7; $i++) {
            $days[] = [
                'date' => $currentDay->format('Y-m-d'),
                'dayName' => ucfirst($currentDay->translatedFormat('D')),
                'dayNumber' => $currentDay->format('d/m'),
                'isToday' => $currentDay->isToday(),
            ];
            $currentDay->addDay();
        }

        $hours = range(8, 18);

        $query = Deal::query()
            ->whereIn('status', [DealStatus::PENDING, DealStatus::NEGOTIATING])
            ->with(['notesList' => fn ($q) => $q->orderBy('created_at', 'desc'), 'user', 'client']);

        if ($user->profile === UserProfile::USER) {
            $query->where('user_id', $user->id);
        } else {
            $query->whereHas('user', fn ($q) => $q->where('profile', UserProfile::USER));
        }

        $notes = $query->get()
            ->map(fn ($deal) => $deal->notesList->first())
            ->filter(fn ($note) => $note && $note->next_follow_up_date && $note->next_follow_up_date->between($startOfWeek, $endOfWeek));

        $matrix = [];
        foreach ($notes as $note) {
            $date = Carbon::parse($note->next_follow_up_date);
            $dayKey = $date->format('Y-m-d');
            $hourKey = (int) $date->format('H');

            if (! isset($matrix[$dayKey])) {
                $matrix[$dayKey] = [];
            }
            if (! isset($matrix[$dayKey][$hourKey])) {
                $matrix[$dayKey][$hourKey] = [];
            }

            $matrix[$dayKey][$hourKey][] = $note;
        }

        return [
            'startOfWeek' => $startOfWeek->format('d/m/Y'),
            'endOfWeek' => $endOfWeek->format('d/m/Y'),
            'days' => $days,
            'hours' => $hours,
            'matrix' => $matrix,
        ];
    }

    public function render()
    {
        $user = Auth::user();

        if (! $user) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        $overdueContacts = $this->overdueContacts;
        $overdueCount = $overdueContacts->count();
        $weeklySchedule = $this->weeklySchedule;

        return view('livewire.pending-contacts-header-badge', [
            'overdueContacts' => $overdueContacts,
            'overdueCount' => $overdueCount,
            'weeklySchedule' => $weeklySchedule,
            'selectedNote' => $this->selectedNoteId ? DealNote::with(['user', 'deal.client'])->find($this->selectedNoteId) : null,
        ]);
    }

    public function addNoteAction(): Action
    {
        return Action::make('addNote')
            ->label('Registrar Contato')
            ->modalHeading('Registrar Novo Contato')
            ->modalWidth(Width::Medium)
            ->slideOver()
            ->schema(fn ($schema) => NotesForm::configure($schema, true))
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
}
