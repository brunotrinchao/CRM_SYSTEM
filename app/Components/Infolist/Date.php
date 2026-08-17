<?php

namespace App\Components\Infolist;

use Filament\Infolists\Components\TextEntry;

class Date
{
    /**
     * TextEntry de data padronizado.
     *
     * Config suportada:
     * - format: string (default 'd/m/Y')
     * - withTime: bool (default false)
     * - hiddenLabel: bool (default false)
     * - columnSpan: int|string
     * - columnSpanFull: bool
     */
    public static function make(string $name, ?string $label = null, array $config = []): TextEntry
    {
        $entry = TextEntry::make($name)
            ->label($label)
            ->hiddenLabel($config['hiddenLabel'] ?? false)
            ->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1));

        if ($config['withTime'] ?? false) {
            $entry->dateTime($config['format'] ?? 'd/m/Y H:i');
        } else {
            $entry->date($config['format'] ?? 'd/m/Y');
        }

        return $entry;
    }
}
