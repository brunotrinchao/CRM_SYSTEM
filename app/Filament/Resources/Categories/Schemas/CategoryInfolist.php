<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Components\Card;
use App\Components\Infolist\Date;
use App\Components\Infolist\Icon;
use App\Components\Infolist\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Card::make('Informações da Categoria', [
                    Text::make('name', 'Nome'),
                    Text::make('description', 'Descrição', [
                        'columnSpanFull' => true,
                    ]),
                    Icon::make('active', 'Ativo'),
                    Date::make('created_at', 'Criado em', [
                        'withTime' => true,
                    ]),
                ], [
                    'icon' => Heroicon::Tag,
                    'columns' => 2,
                ]),
            ]);
    }
}
