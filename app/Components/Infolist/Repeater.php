<?php

namespace App\Components\Infolist;

use Filament\Infolists\Components\RepeatableEntry;

class Repeater
{
    /**
     * RepeatableEntry (Repeater de exibição) — Infolist.
     *
     * Uso:
     *   Repeater::make('addresses', 'Endereços', [
     *       Text::make('street', 'Rua'),
     *       Text::make('zip_code', 'CEP'),
     *   ])
     *
     * Config suportada:
     * - columns: int (grid interno, default 1)
     * - columnSpan / columnSpanFull: layout no infolist pai
     */
    public static function make(string $name, ?string $label = null, array $fields = [], array $config = []): RepeatableEntry
    {
        $entry = RepeatableEntry::make($name)
            ->schema($fields)
            ->grid($config['columns'] ?? 1);

        if($label){
          $entry->label($label);
        }else{
          $entry->hiddenLabel();
        }

        $entry->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 'full'));

        return $entry;
    }
}
