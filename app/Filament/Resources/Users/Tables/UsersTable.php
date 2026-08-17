<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserProfile;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Models\User;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('profile')
                    ->label('Perfil')
                    ->badge()
                    ->formatStateUsing(fn (UserProfile $state): string => $state->label())
                    ->color(fn (UserProfile $state): string => $state->color()),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('profile')
                    ->label('Perfil')
                    ->options(UserProfile::options()),
            ])
            ->recordUrl(null)
            ->recordAction('custom_view')
            ->recordActions([
                Impersonate::make(),
                SimpleActions::getViewWithEditAndDelete(
                    width: Width::Large,
                    schemaCallback: fn ($schema) => UserForm::configure($schema),
                    schemaViewCallback: fn ($schema) => UserInfolist::configure($schema),
                    actionCallback: function (User $record, array $data) {
                        // 1. O perfil do usuário ADMIN não pode ser alterado por ninguém
                        if ($record->profile === UserProfile::ADMIN) {
                            return $record;
                        }

                        // 2. Apenas ADMIN pode transformar um usuário em ADMIN
                        if (isset($data['profile']) && $data['profile'] === UserProfile::ADMIN->value) {
                            if (Auth::user()?->profile !== UserProfile::ADMIN) {
                                return $record;
                            }
                        }

                        if (isset($data['profile'])) {
                            $record->update([
                                'profile' => $data['profile'],
                            ]);
                        }

                        return $record;
                    },
                    model: User::class,
                    recordName: 'Usuário',
                    modal: false
                ),
            ]);
    }
}
