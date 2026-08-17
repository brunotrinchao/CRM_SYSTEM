<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum ChannelNote: string implements \Filament\Support\Contracts\HasLabel
{
    use HasLabel;

    case CALL = 'CALL';
    case MEETING = 'MEETING';
    case WHATSAPP = 'WHATSAPP';
    case EMAIL = 'EMAIL';
    case VISIT = 'VISIT';
    case OTHER = 'OTHER';

    public function getLabel(): string
    {
        return match ($this) {
            self::CALL => 'Ligação telefônica',
            self::MEETING => 'Reunião',
            self::WHATSAPP => 'WhatsApp',
            self::EMAIL => 'E-mail',
            self::VISIT => 'Visita presencial',
            self::OTHER => 'Outros',
        };
    }
}
