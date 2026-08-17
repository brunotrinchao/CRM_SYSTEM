<?php

namespace App\Traits;

use App\Enums\DiscountRequestStatus;
use App\Models\User;
use App\Enums\UserProfile;
use App\Notifications\SystemActivityNotification;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Notification;

trait NotificationResolveTrait
{
    protected function dispatchNotification(?int $ownerId, string $title, string $body, string $type = 'create', ?Action $action = null): void
    {
        [$icon, $color] = match ($type) {
            'create' => ['heroicon-o-check', 'success'],
            'update' => ['heroicon-o-pencil-square', 'info'],
            'delete' => ['heroicon-o-trash', 'danger'],
            'warning' => ['heroicon-o-exclamation-triangle', 'warning'],
            default => ['heroicon-o-information-circle', 'primary'],
        };

        // Busca o dono da ação e todos os administradores/gerentes
        $recipients = User::query()
            ->where(function ($query) use ($ownerId) {
                if ($ownerId) {
                    $query->where('id', $ownerId);
                }
                $query->orWhereIn('profile', [UserProfile::ADMIN->value, UserProfile::MANAGER->value]);
            })
            ->get();

        Notification::send($recipients, new SystemActivityNotification($title, $body, $icon, $color, $action));
    }

    protected function dispatchDiscountNotification(?int $ownerId, string $title, string $body, DiscountRequestStatus $status): void
    {

        [$icon, $color] = match ($status) {
            DiscountRequestStatus::PENDING => ['heroicon-o-clock', 'warning'],
            DiscountRequestStatus::APPROVED => ['heroicon-o-check', 'success'],
            DiscountRequestStatus::REJECTED => ['heroicon-o-x-circle', 'danger'],
            default => ['heroicon-o-exclamation-triangle', 'warning'],
        };

     
        $recipients = User::query()
            ->where(function ($query) use ($ownerId) {
                if ($ownerId) {
                    $query->where('id', $ownerId);
                }
                $query->orWhereIn('profile', [UserProfile::ADMIN->value, UserProfile::MANAGER->value]);
            })
            ->get();

        Notification::send($recipients, new SystemActivityNotification($title, $body, $icon, $color));
    }
}
