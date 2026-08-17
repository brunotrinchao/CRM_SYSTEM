<?php

namespace App\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class Money
{
    /**
     * TextEntry monetário padronizado.
     *
     * Config suportada:
     * - currency: string (default 'BRL')
     * - hiddenLabel: bool (default false)
     * - columnSpan: int|string
     * - columnSpanFull: bool
     */
    public static function make(string $name, ?string $label = null, array $config = []): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->hiddenLabel($config['hiddenLabel'] ?? false)
            ->money($config['currency'] ?? 'BRL')
            ->extraAttributes(fn (): array => ['class' => 'font-finance'])
            ->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1));
    }
}
