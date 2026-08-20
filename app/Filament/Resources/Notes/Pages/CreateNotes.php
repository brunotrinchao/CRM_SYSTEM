<?php

namespace App\Filament\Resources\Notes\Pages;

use App\Filament\Resources\Notes\NotesResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateNotes extends CreateRecord
{
    protected static string $resource = NotesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }
}
