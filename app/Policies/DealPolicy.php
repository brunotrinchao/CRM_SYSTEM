<?php

namespace App\Policies;

use App\Enums\UserProfile;
use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Deal $deal): bool
    {
        if ($user->profile === UserProfile::ADMIN || $user->profile === UserProfile::MANAGER) {
            return true;
        }

        return (int) $deal->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can create models.
     * Negócio NUNCA pode ser criado por um perfil USER. Apenas ADMIN e MANAGER.
     */
    public function create(User $user): bool
    {
        return $user->profile === UserProfile::ADMIN || $user->profile === UserProfile::MANAGER;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Deal $deal): bool
    {
        if ($user->profile === UserProfile::ADMIN || $user->profile === UserProfile::MANAGER) {
            return true;
        }

        return (int) $deal->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Deal $deal): bool
    {
        return $user->profile === UserProfile::ADMIN || $user->profile === UserProfile::MANAGER;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Deal $deal): bool
    {
        return $user->profile === UserProfile::ADMIN || $user->profile === UserProfile::MANAGER;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Deal $deal): bool
    {
        return $user->profile === UserProfile::ADMIN;
    }
}
