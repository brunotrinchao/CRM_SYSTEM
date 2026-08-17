<?php

namespace App\Filament\Resources\Deals\Schemas;

use App\Components\Form\Select;
use App\Models\Client;
use App\Models\User;
use Bjanczak\FilamentFlexFields\Enums\ControlSize;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\UserSelect;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;

class DealTransfer
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                UserSelect::make('current_user_id')
                ->label('De')
                ->optionModel(User::class)
                ->nameColumn('name')
                ->emailColumn('email')
                ->disabled() // Apenas para visualização
                ->dehydrated(false)
                ->size(ControlSize::Lg),
                
                UserSelect::make('user_id')
                ->label('Novo Responsável')
                ->optionModel(User::class)
                ->nameColumn('name')
                ->emailColumn('email')
                ->placeholder('Selecione o novo usuário...')
                ->required()
                ->helperText('O negócio será reatribuído imediatamente para este usuário.')
                ->size(ControlSize::Lg),
            ]);
    }
}
