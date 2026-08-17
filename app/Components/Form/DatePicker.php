<?php

namespace App\Components\Form;

use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexDatePicker;
use Illuminate\Support\Str;

class DatePicker
{
    /**
     * Seletor de data (FlexDatePicker do plugin).
     *
     * Config suportada:
     * - required: bool
     * - default: mixed
     * - min: string
     * - max: string
     * - helperText: string
     * - disabled: bool
     * - columnSpan: int|string
     * - columnSpanFull: bool
     */
    public static function make(string $name, ?string $label = null, array $config = []): FlexDatePicker
    {
        $field = FlexDatePicker::make($name)
        ->size('lg')
            ->label($label ?? Str::title(str_replace('_', ' ', $name)))
            ->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1));

        if ($config['required'] ?? false) {
            $field->required();
        }

        if (array_key_exists('default', $config)) {
            $field->default($config['default']);
        }

        if (($config['min'] ?? null) && method_exists($field, 'minDate')) {
            $field->minDate($config['min']);
        }

        if (($config['max'] ?? null) && method_exists($field, 'maxDate')) {
            $field->maxDate($config['max']);
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
