<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Components\Card;
use App\Components\Form\Textarea;
use App\Components\Form\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Card::make('Informações da Categoria', [
                    TextInput::make('name', 'Nome', [
                        'required' => true,
                        'maxLength' => 255,
                        'prefixIcon' => Heroicon::Tag,
                    ]),
                    Textarea::make('description', 'Descrição', [
                        'maxLength' => 500,
                        'footer' => 'Click no microfone para começar a mensagem.',
                    ]),
                ]),
            ]);
    }
}
