<?php

namespace App\Enums;

use App\Enums\Concerns\HasColor;
use App\Enums\Concerns\HasIcon;
use App\Enums\Concerns\HasLabel;
use Filament\Support\Icons\Heroicon;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum ClientOrigin: string implements \Filament\Support\Contracts\HasLabel
{
    use HasColor;
    use HasIcon;
    use HasLabel;

    case GOOGLE = 'GOOGLE';
    case FACEBOOK = 'FACEBOOK';
    case INDICACAO = 'INDICACAO';
    case SITE = 'SITE';
    case OLX = 'OLX';
    case TELEGRAM = 'TELEGRAM';
    case WHATSAPP = 'WHATSAPP';
    case MERCADO_LIVRE = 'MERCADO_LIVRE';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::GOOGLE => 'Google',
            self::FACEBOOK => 'Facebook',
            self::INDICACAO => 'Indicação',
            self::SITE => 'Site',
            self::OLX => 'OLX',
            self::TELEGRAM => 'Telegram',
            self::WHATSAPP => 'WhatsApp',
            self::MERCADO_LIVRE => 'Mercado Livre',
            self::OTHER => 'Outro',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::GOOGLE => 'google',
            self::FACEBOOK => 'facebook',
            self::INDICACAO => 'indicacao',
            self::SITE => 'site',
            self::OLX => 'olx',
            self::TELEGRAM => 'telegram',
            self::WHATSAPP => 'whatsapp',
            self::MERCADO_LIVRE => 'mercado_livre',
            self::OTHER => 'other',
            default => 'gray'
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::GOOGLE => Phosphor::GoogleLogoThin,
            self::FACEBOOK => Phosphor::FacebookLogoThin,
            self::INDICACAO => Phosphor::StarThin,
            self::SITE => Phosphor::BrowserThin,
            self::OLX => Heroicon::Tag,
            self::TELEGRAM => Phosphor::TelegramLogoThin,
            self::WHATSAPP => Phosphor::WhatsappLogoThin,
            self::MERCADO_LIVRE => Heroicon::ShoppingBag,
            self::OTHER => Heroicon::EllipsisHorizontal,
        };
    }
}
