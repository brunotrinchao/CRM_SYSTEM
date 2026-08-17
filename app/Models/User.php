<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserProfile;
use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'profile', 'avatar_url'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, ProvidesActivityTitle, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'profile' => \App\Enums\UserProfile::class
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->profile !== null && UserProfile::tryFrom($this->profile->value ?? '') !== null;
    }

    public function canImpersonate(): bool
    {
        return in_array($this->profile, [UserProfile::ADMIN, UserProfile::MANAGER]);
    }

    public function canBeImpersonated(): bool
    {
        return true;
    }

    public function getIcon(): \ToneGabes\Filament\Icons\Enums\Phosphor
    {
        return \ToneGabes\Filament\Icons\Enums\Phosphor::UserGearDuotone;
    }

    public function activityTitle(): ?string
    {
        return $this->name;
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (! $this->avatar_url) {
            return null;
        }

        if (str_starts_with($this->avatar_url, 'http://') || str_starts_with($this->avatar_url, 'https://')) {
            return $this->avatar_url;
        }

        return Storage::url($this->avatar_url);
    }
}
