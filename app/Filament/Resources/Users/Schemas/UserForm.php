<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Components\Form\Select;
use App\Components\Form\TextInput;
use App\Enums\UserProfile;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name', 'Nome', [
                    'prefixIcon' => Heroicon::User,
                    'disabled' => fn ($record) => $record !== null,
                    'required' => true,
                ]),
                TextInput::make('email', 'E-mail', [
                    'type' => 'email',
                    'prefixIcon' => Heroicon::Envelope,
                    'disabled' => fn ($record) => $record !== null,
                    'required' => true,
                ]),
                TextInput::make('password', 'Senha', [
                    'type' => 'password',
                    'prefixIcon' => Heroicon::LockClosed,
                    'required' => fn ($record) => $record === null,
                    'hidden' => fn ($record) => $record !== null,
                ]),
                Select::make('profile', 'Perfil de Acesso', [
                    'options' => function () {
                        $options = UserProfile::options();

                        // Apenas ADMIN pode transformar um usuário em ADMIN
                        if (Auth::user()?->profile !== UserProfile::ADMIN) {
                            unset($options[UserProfile::ADMIN->value]);
                        }

                        return $options;
                    },
                    'required' => true,
                    'native' => false,
                    'disabled' => fn ($record) => $record?->profile === UserProfile::ADMIN,
                ]),
            ]);
    }
}
