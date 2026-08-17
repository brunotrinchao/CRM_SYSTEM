<?php

namespace App\Enums;

use App\Enums\Concerns\HasColor;
use App\Enums\Concerns\HasIcon;
use App\Enums\Concerns\HasLabel;
use Filament\Support\Icons\Heroicon;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum AddressType: string implements \Filament\Support\Contracts\HasLabel
{
    use HasColor;
    use HasIcon;
    use HasLabel;

    case RESIDENCE = 'RESIDENCE';
    case COMMERCIAL = 'COMMERCIAL';
    case DELIVERY = 'DELIVERY';
    case BILLING = 'BILLING';

    public function label(): string
    {
        return match ($this) {
            self::RESIDENCE => 'Residencial',
            self::COMMERCIAL => 'Comercial',
            self::DELIVERY => 'Entrega',
            self::BILLING => 'Cobrança',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RESIDENCE => 'primary',
            self::COMMERCIAL => 'info',
            self::DELIVERY => 'warning',
            self::BILLING => 'gray',
        };
    }

    public function icon(): Phosphor
    {
        return match ($this) {
            self::RESIDENCE => Phosphor::HouseThin,
            self::COMMERCIAL => Phosphor::BuildingOfficeThin,
            self::DELIVERY => Phosphor::TruckThin,
            self::BILLING => Phosphor::MoneyFill,
        };
    }
}
