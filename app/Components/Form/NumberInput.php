<?php

namespace App\Components\Form;

use Bjanczak\FilamentFlexFields\Filament\Forms\Components\CalculatorField;
use Illuminate\Support\Str;

class NumberInput
{
    /**
     * Campo numérico com calculadora (CalculatorField do plugin).
     *
     * Config suportada:
     * - required: bool
     * - default: mixed
     * - min: int|float
     * - max: int|float
     * - step: int|float
     * - integer: bool (default true)
     * - decimalPlaces: int
     * - helperText: string
     * - placeholder: string
     * - disabled: bool
     * - prefix: string
     * - suffix: string
     * - variant: string ('primary'|'secondary'|'flat'|'soft')
     * - columnSpan: int|string
     * - columnSpanFull: bool
     */
    public static function make(string $name, ?string $label = null, array $config = []): CalculatorField
    {
        $field = CalculatorField::make($name)
        ->size('lg')
            ->label($label ?? Str::title(str_replace('_', ' ', $name)))
            ->integer($config['integer'] ?? true)
            ->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1));

        if ($config['required'] ?? false) {
            $field->required();
        }

        if (array_key_exists('default', $config)) {
            $field->default($config['default']);
        }

        if ($config['min'] ?? null) {
            $field->minValue($config['min']);
        }

        if ($config['max'] ?? null) {
            $field->maxValue($config['max']);
        }

        if ($config['step'] ?? null) {
            $field->step($config['step']);
        }

        if ($config['decimalPlaces'] ?? null) {
            $field->decimalPlaces($config['decimalPlaces']);
        }

        if ($config['helperText'] ?? null) {
            $field->helperText($config['helperText']);
        }

        if ($config['placeholder'] ?? null) {
            $field->placeholder($config['placeholder']);
        }

        if ($config['disabled'] ?? false) {
            $field->disabled();
        }

        if ($config['prefix'] ?? null) {
            $field->prefix($config['prefix']);
        }

        if ($config['suffix'] ?? null) {
            $field->suffix($config['suffix']);
        }

        if ($config['variant'] ?? null) {
            $field->variant($config['variant']);
        }

        return $field;
    }
}
