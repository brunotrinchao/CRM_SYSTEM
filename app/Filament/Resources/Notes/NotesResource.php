<?php

namespace App\Filament\Resources\Notes;

use App\Filament\Resources\Notes\Pages\CreateNotes;
use App\Filament\Resources\Notes\Pages\EditNotes;
use App\Filament\Resources\Notes\Pages\ListNotes;
use App\Filament\Resources\Notes\Schemas\NotesForm;
use App\Filament\Resources\Notes\Schemas\NotesInfolist;
use App\Filament\Resources\Notes\Tables\NotesTable;
use App\Models\DealNote;
use App\Models\Notes;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use UnitEnum;

class NotesResource extends Resource
{
    protected static ?string $model = DealNote::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::PhoneListDuotone;

    protected static ?string $navigationLabel = 'Contatos';
    
    protected static ?string $modelLabel = 'Contato';
    protected static ?string $pluralModelLabel = 'Contatos';

    protected static string | UnitEnum | null $navigationGroup = 'Administração';

    protected static ?string $recordTitleAttribute = 'interaction_type';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'interaction_type',
            'contact_date',
            'content'
        ];
    }


    public static function shouldRegisterNavigation(): bool
    {
        return true; // Remove o resource do menu lateral
    }

    public static function form(Schema $schema): Schema
    {
        return NotesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NotesInfolist::configure($schema);
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
