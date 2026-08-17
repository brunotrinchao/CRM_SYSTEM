<?php

namespace App\Enums;

use App\Enums\Concerns\HasColor;
use App\Enums\Concerns\HasIcon;
use App\Enums\Concerns\HasLabel;
use Filament\Support\Icons\Heroicon;

enum InteractionType: string implements \Filament\Support\Contracts\HasLabel
{
    use HasColor;
    use HasIcon;
    use HasLabel;

    case CALL = 'CALL';
    case MEETING = 'MEETING';
    case WHATSAPP = 'WHATSAPP';
    case EMAIL = 'EMAIL';
    case VISIT = 'VISIT';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::CALL => 'Ligação',
            self::MEETING => 'Reunião',
            self::WHATSAPP => 'WhatsApp',
            self::EMAIL => 'E-mail',
            self::VISIT => 'Visita',
            self::OTHER => 'Outro',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CALL => 'primary',
            self::MEETING => 'warning',
            self::WHATSAPP => 'success',
            self::EMAIL => 'info',
            self::VISIT => 'gray',
            self::OTHER => 'gray',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::CALL => Heroicon::Phone,
            self::MEETING => Heroicon::Calendar,
            self::WHATSAPP => Heroicon::ChatBubbleLeftEllipsis,
            self::EMAIL => Heroicon::Envelope,
            self::VISIT => Heroicon::MapPin,
            self::OTHER => Heroicon::EllipsisHorizontal,
        };
    }
}
