<?php

namespace App\Filament\Resources\Notes\Pages;

use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Notes\NotesResource;
use App\Filament\Resources\Notes\Schemas\NotesForm;
use App\Models\DealNote;
use App\Services\DealNoteService;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListNotes extends ListRecords
{
    protected static string $resource = NotesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SimpleActions::getCreateModal(
                width: Width::Large,
                schemaCallback: fn ($schema) => NotesForm::configure($schema, isDealForm: false),
                actionCallback: fn (array $data) => DealNoteService::create($data),
                recordName: 'Contato',
                model: DealNote::class,
                modal: false,
                name: 'create_note_modal',
            )
        ];
    }
}
