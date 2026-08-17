<?php

namespace App\Enums;

use App\Enums\Concerns\HasColor;
use App\Enums\Concerns\HasIcon;
use App\Enums\Concerns\HasLabel;
use Filament\Support\Icons\Heroicon;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum UserProfile: string implements \Filament\Support\Contracts\HasLabel
{
    use HasColor;
    use HasIcon;
    use HasLabel;

    case ADMIN = 'ADMIN';
    case MANAGER = 'MANAGER';
    case USER = 'USER';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::MANAGER => 'Gerente',
            self::USER => 'Usuário',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'danger',
            self::MANAGER => 'warning',
            self::USER => 'primary',
        };
    }

    public function icon(): Phosphor
    {
        return match ($this) {
            self::ADMIN => Phosphor::ShieldThin,
            self::MANAGER => Phosphor::UsersThin,
            self::USER => Phosphor::UserThin,
        };
    }
}
