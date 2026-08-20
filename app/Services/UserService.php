<?php

namespace App\Services;

use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public static function create(array $data): User
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            $data['password'] = Hash::make(Str::random(16));
        }

        // Apenas ADMIN pode criar usuários com perfil ADMIN
        if (isset($data['profile']) && $data['profile'] === UserProfile::ADMIN->value) {
            if (Auth::user()?->profile !== UserProfile::ADMIN) {
                $data['profile'] = UserProfile::SELLER->value;
            }
        }

        return User::create($data);
    }

    public static function update(User $user, array $data): User
    {
        if ($user->profile === UserProfile::ADMIN) {
            unset($data['profile']);
        }

        if (isset($data['profile']) && $data['profile'] === UserProfile::ADMIN->value) {
            if (Auth::user()?->profile !== UserProfile::ADMIN) {
                unset($data['profile']);
            }
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }
}
