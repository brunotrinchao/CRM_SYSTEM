<?php

namespace App\Components\Form;

use Bjanczak\FilamentFlexFields\Filament\Forms\Components\SwitchField;
use Illuminate\Support\Str;

class Toggle
{
    /**
     * Switch rico (SwitchField do plugin).
     *
     * Config suportada:
     * - required: bool
     * - default: bool
     * - helperText: string
     * - disabled: bool
     * - columnSpan: int|string
     * - columnSpanFull: bool
     * - onColor / offColor: string
     * - onIcon / offIcon: BackedEnum|string
     * - variant: string
     * - description: string
     */
    public static function make(string $name, ?string $label = null, array $config = []): SwitchField
    {
        $field = SwitchField::make($name)
            ->label($label ?? Str::title(str_replace('_', ' ', $name)))
            ->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1));

        if ($config['required'] ?? false) {
            $field->required();
        }

        if (array_key_exists('default', $config)) {
            $field->default($config['default']);
        }

        if ($config['helperText'] ?? null) {
            $field->helperText($config['helperText']);
        }

        if ($config['disabled'] ?? false) {
            $field->disabled();
        }

        if ($config['onColor'] ?? null) {
            $field->onColor($config['onColor']);
        }

        if ($config['offColor'] ?? null) {
            $field->offColor($config['offColor']);
        }

        if ($config['onIcon'] ?? null) {
            $field->onIcon($config['onIcon']);
        }

        if ($config['offIcon'] ?? null) {
            $field->offIcon($config['offIcon']);
        }

        if ($config['variant'] ?? null) {
            $field->variant($config['variant']);
        }

        if ($config['description'] ?? null) {
            $field->description($config['description']);
        }

        return $field;
    }
}
