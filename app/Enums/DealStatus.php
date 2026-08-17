<?php

namespace App\Enums;

use App\Enums\Concerns\HasColor;
use App\Enums\Concerns\HasIcon;
use App\Enums\Concerns\HasLabel;
use Filament\Support\Icons\Heroicon;

enum DealStatus: string implements \Filament\Support\Contracts\HasLabel
{
    use HasColor;
    use HasIcon;
    use HasLabel;

    case PENDING = 'PENDING';
    case NEGOTIATING = 'NEGOTIATING';
    case WON = 'WON';
    case LOST = 'LOST';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::NEGOTIATING => 'Negociação',
            self::WON => 'Ganho',
            self::LOST => 'Perdido',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::NEGOTIATING => 'warning',
            self::WON => 'success',
            self::LOST => 'danger',
            self::CANCELLED => 'mute',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::PENDING => Heroicon::PauseCircle,
            self::NEGOTIATING => Heroicon::HandRaised,
            self::WON => Heroicon::HandThumbUp,
            self::LOST => Heroicon::HandThumbDown,
            self::CANCELLED => Heroicon::XCircle,
        };
    }
}
