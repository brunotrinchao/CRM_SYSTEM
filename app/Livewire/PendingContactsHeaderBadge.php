<?php

namespace App\Livewire;

use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Models\Deal;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PendingContactsHeaderBadge extends Component
{
    public function render()
    {
        $user = Auth::user();

        if (! $user) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        $query = Deal::query()
            ->whereIn('status', [DealStatus::PENDING, DealStatus::NEGOTIATING])
            ->with(['notesList' => fn ($q) => $q->orderBy('created_at', 'desc')]);

        if ($user->profile === UserProfile::USER) {
            $query->where('user_id', $user->id);
        } else {
            $query->whereHas('user', fn ($q) => $q->where('profile', UserProfile::USER));
        }

        $now = now();

        $count = $query->get()->filter(function ($deal) use ($now) {
            $latestNote = $deal->notesList->first();
            $nextContact = $latestNote?->next_follow_up_date;

            if (! $nextContact) {
                return true;
            }

            return $nextContact->isPast() || $nextContact->isToday();
        })->count();

        return view('livewire.pending-contacts-header-badge', [
            'count' => $count,
        ]);
    }
}
