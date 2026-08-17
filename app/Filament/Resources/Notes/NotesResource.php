<?php

namespace App\Filament\Resources\Notes;

use App\Filament\Resources\Notes\Pages\CreateNotes;
use App\Filament\Resources\Notes\Pages\EditNotes;
use App\Filament\Resources\Notes\Pages\ListNotes;
use App\Filament\Resources\Notes\Schemas\NotesForm;
use App\Filament\Resources\Notes\Tables\NotesTable;
use App\Models\Notes;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotesResource extends Resource
{
    protected static ?string $model = Notes::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // protected static ?string $recordTitleAttribute = 'DealNote';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Remove o resource do menu lateral
    }

    public static function form(Schema $schema): Schema
    {
        return NotesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotes::route('/'),
            'create' => CreateNotes::route('/create'),
            'edit' => EditNotes::route('/{record}/edit'),
        ];
    }
}
