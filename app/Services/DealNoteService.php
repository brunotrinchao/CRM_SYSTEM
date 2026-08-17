<?php

namespace App\Services;

use App\Models\DealNote;
use Illuminate\Support\Facades\Auth;

class DealNoteService
{
    public static function create(array $data): DealNote
    {
        $data['user_id'] = Auth::id();

        $note = DealNote::create($data);

        if (isset($data['deal_id'])) {
            $deal = \App\Models\Deal::find($data['deal_id']);
            if ($deal && $deal->status === \App\Enums\DealStatus::PENDING) {
                $deal->update([
                    'status' => \App\Enums\DealStatus::NEGOTIATING,
                ]);
            }
        }

        return $note;
    }

    public static function update(DealNote $deal, array $data): DealNote
    {
        $deal->update($data);

        return $deal;
    }
}

