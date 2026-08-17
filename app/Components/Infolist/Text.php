<?php

namespace App\Components\Infolist;

use Filament\Infolists\Components\TextEntry;
use Filament\Support\Colors\Color;
use Illuminate\Support\Str;

class Text
{
    /**
     * TextEntry padronizado para slideOver.
     *
     * Config suportada:
     * - hiddenLabel: bool (default false)
     * - placeholder: string (default '-')
     * - format: string (ex 'd/m/Y', 'd/m/Y H:i')
     * - dateTime: bool (usa dateTime com formato)
     * - date: bool (usa date com formato)
     * - money: bool|string (currency)
     * - numeric: bool
     * - badge: bool
     * - color: string|Closure
     * - icon: BackedEnum|string
     * - weight / size: string
     * - columnSpan: int|string
     * - columnSpanFull: bool
     */
    public static function make(string $name, ?string $label = null, array $config = []): TextEntry
    {
        $entry = TextEntry::make($name)
            ->label(strtoupper($label) ?? Str::title(str_replace('_', ' ', strtoupper($name))))
            ->hiddenLabel($config['hiddenLabel'] ?? false)
            ->placeholder($config['placeholder'] ?? '-')
            ->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1));

            if($config['phone'] ?? false){
                $entry->formatStateUsing(function (?string $state): ?string {
                    if (!$state) {
                        return null;
                    }

                    // Remove tudo o que não for número
                    $phone = preg_replace('/\D/', '', $state);

                    if (str_starts_with($phone, '55') && (strlen($phone) === 12 || strlen($phone) === 13)) {
                        $phone = substr($phone, 2);
                    }

                    // Aplica a máscara dependendo se tem 11 dígitos (celular) ou 10 (fixo/antigo)
                    if (strlen($phone) === 11) {
                        return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $phone);
                    }

                    if (strlen($phone) === 10) {
                        return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $phone);
                    }

                    // Retorna o original caso não se encaixe nos tamanhos esperados
                    return $state;
                });
        }

        if ($config['money'] ?? false) {
            $entry->money($config['money'] === true ? 'BRL' : $config['money']);
        }

        if ($config['dateTime'] ?? false) {
            $entry->dateTime($config['format'] ?? 'd/m/Y H:i');
        } elseif ($config['date'] ?? false) {
            $entry->date($config['format'] ?? 'd/m/Y');
        }

        if ($config['numeric'] ?? false) {
            $entry->numeric();
        }

        if ($config['badge'] ?? false) {
            $entry->badge();
        }

        if ($config['color'] ?? null) {
            $entry->color($config['color']);
        }

        if ($config['icon'] ?? null) {
            $entry->icon($config['icon']);
        }

        if ($config['weight'] ?? null) {
            $entry->weight($config['weight']);
        }

        if ($config['size'] ?? null) {
            $entry->size($config['size']);
        }

        return $entry;
    }
}
