<?php

namespace App\Enums;

use App\Enums\Concerns\HasColor;
use App\Enums\Concerns\HasIcon;
use App\Enums\Concerns\HasLabel;
use Filament\Support\Icons\Heroicon;

enum DiscountRequestStatus: string implements \Filament\Support\Contracts\HasLabel
{
    use HasColor;
    use HasIcon;
    use HasLabel;

    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::APPROVED => 'Aprovado',
            self::REJECTED => 'Rejeitado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::PENDING => Heroicon::Clock,
            self::APPROVED => Heroicon::CheckCircle,
            self::REJECTED => Heroicon::XCircle,
        };
    }
}
