<?php

namespace App\Filament\Resources\Deals\Pages;

use App\Filament\Resources\Deals\DealResource;
use App\Models\Deal;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDeal extends CreateRecord
{
    protected static string $resource = DealResource::class;

    protected function handleRecordCreation(array $data): Deal
    {
        $data['user_id'] = Auth::id();
        return Deal::create($data);
    }

    #[\Livewire\Attributes\On('assign-seller')]
    public function assignSeller(int $sellerId): void
    {
        $this->data['user_id'] = $sellerId;

        try {
            $this->unmountAction(cancelParentActions: false);
        } catch (\Throwable $e) {}
    }
}
