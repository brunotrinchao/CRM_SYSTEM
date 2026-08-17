<?php

namespace App\Components\Form;

use Bjanczak\FilamentFlexFields\Filament\Forms\Components\CurrencyField;
use Illuminate\Support\Str;

class CurrencyInput
{
    /**
     * Campo monetário (CurrencyField do plugin).
     *
     * Config suportada:
     * - currency: string (default 'BRL')
     * - locale: string (default 'pt_BR')
     * - required: bool
     * - default: mixed
     * - min: float
     * - max: float
     * - allowNegative: bool
     * - searchable: bool
     * - helperText: string
     * - disabled: bool
     * - columnSpan: int|string
     * - columnSpanFull: bool
     */
    public static function make(string $name, ?string $label = null, array $config = []): CurrencyField
    {
        $field = CurrencyField::make($name)

        ->size('lg')
            ->label($label ?? Str::title(str_replace('_', ' ', $name)))
            ->currency($config['currency'] ?? 'BRL')
            ->locale($config['locale'] ?? 'pt_BR')
            ->searchable($config['searchable'] ?? false)
            ->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1))
            // O plugin opera em centavos (minor units) no state, mas a coluna
            // decimal(10,2) guarda reais (major units). Converte na gravação:
            // centavos → reais (÷100).
            ->dehydrateStateUsing(function ($state) {
                if ($state === null || $state === '') {
                    return null;
                }

                return round((float) $state / 100, 2);
            });

        if ($config['required'] ?? false) {
            $field->required();
        }

        if (array_key_exists('default', $config)) {
            $field->default($config['default']);
        }

        if ($config['min'] ?? null) {
            $field->min($config['min']);
        }

        if ($config['max'] ?? null) {
            $field->max($config['max']);
        }

        if ($config['allowNegative'] ?? false) {
            $field->allowNegative();
        }

        if ($config['helperText'] ?? null) {
            $field->helperText($config['helperText']);
        }

        if ($config['disabled'] ?? false) {
            $field->disabled();
        }

        return $field;
    }
}
