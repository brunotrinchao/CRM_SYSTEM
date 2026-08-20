<?php

namespace App\Services;

use App\Models\DealNote;
use Illuminate\Support\Facades\Auth;

class DealNoteService
{
    public static function create(array $data): DealNote
    {
        if (empty($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $note = DealNote::create($data);

        if (isset($data['deal_id'])) {
            $deal = \App\Models\Deal::find($data['deal_id']);
            if ($deal) {
                $updateData = [
                    'last_contact_date' => $note->contact_date ?? $data['contact_date'] ?? now(),
                ];

                if ($deal->status === \App\Enums\DealStatus::PENDING) {
                    $updateData['status'] = \App\Enums\DealStatus::NEGOTIATING;
                }

                $deal->update($updateData);
            }
        }

        return $note;
    }

    public static function update(DealNote $note, array $data): DealNote
    {
        $deal = $note->deal;
        if ($deal && !in_array($deal->status, [\App\Enums\DealStatus::PENDING, \App\Enums\DealStatus::NEGOTIATING], true)) {
            throw new \InvalidArgumentException('Contatos só podem ser editados quando o negócio estiver com status Pendente ou Negociação.');
        }

        $note->update($data);

        return $note;
    }
}

