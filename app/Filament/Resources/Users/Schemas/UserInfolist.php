<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Components\Card;
use App\Components\Infolist\Date;
use App\Components\Infolist\Image;
use App\Components\Infolist\Text;
use App\Enums\UserProfile;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Card::make()
                    ->columns(2)
                    ->schema([
                        Image::make('avatar_url', 'Foto de Perfil', [
                            'circular' => true,
                            'size' => 80,
                            'defaultImageUrl' => fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record?->name ?? 'User'),
                        ]),
                        Text::make('name', 'Nome'),
                        Text::make('email', 'E-mail'),
                        Text::make('profile', 'Perfil de Acesso', [
                            'badge' => true,
                        ])
                        ->color(fn (UserProfile $state): string => $state->color()),
                        Date::make('created_at', 'Cadastrado em', [
                            'withTime' => true,
                        ]),
                    ]),
            ]);
    }
}
