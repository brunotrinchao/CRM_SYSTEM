<?php

namespace App\Enums;

use App\Enums\Concerns\HasColor;
use App\Enums\Concerns\HasIcon;
use App\Enums\Concerns\HasLabel;
use Filament\Support\Icons\Heroicon;

enum DiscountRequestType: string implements \Filament\Support\Contracts\HasLabel
{
    use HasColor;
    use HasIcon;
    use HasLabel;

    case PERCENT = 'PERCENT';
    case VALUE = 'VALUE';

    public function label(): string
    {
        return match ($this) {
            self::PERCENT => 'Percentual',
            self::VALUE => 'Valor fixo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PERCENT => 'info',
            self::VALUE => 'primary',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::PERCENT => Heroicon::PercentBadge,
            self::VALUE => Heroicon::Banknotes,
        };
    }
}
