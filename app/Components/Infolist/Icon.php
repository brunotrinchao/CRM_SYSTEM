<?php

namespace App\Components\Infolist;

use Filament\Infolists\Components\IconEntry;

class Icon
{
    /**
     * IconEntry booleano padronizado.
     *
     * Config suportada:
     * - boolean: bool (default true)
     * - hiddenLabel: bool (default false)
     * - columnSpan: int|string
     * - columnSpanFull: bool
     */
    public static function make(string $name, ?string $label = null, array $config = []): IconEntry
    {
        return IconEntry::make($name)
            ->label($label)
            ->boolean($config['boolean'] ?? true)
            ->hiddenLabel($config['hiddenLabel'] ?? false)
            ->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1));
    }
}
