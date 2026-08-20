<?php

namespace App\Components\Form;

use Bjanczak\FilamentFlexFields\Filament\Forms\Components\SelectField;
use Illuminate\Support\Str;

class Select
{
    /**
     * Select rico (SelectField do plugin).
     *
     * Config suportada:
     * - options: array (opções fixas)
     * - relationship: array [relationship, titleAttribute] (ex: ['category', 'name'])
     * - required: bool
     * - default: mixed
     * - searchable: bool (default true)
     * - preload: bool (default true quando relationship)
     * - placeholder: string
     * - helperText: string
     * - disabled: bool
     * - native: bool (default false; SelectField força false no setUp, pode sobrescrever)
     * - columnSpan: int|string
     * - columnSpanFull: bool
     */
    public static function make(string $name, ?string $label = null, array $config = []): SelectField
    {
        $field = SelectField::make($name)
            ->label($label ?? Str::title(str_replace('_', ' ', $name)))
            ->searchable($config['searchable'] ?? true)
            ->native($config['native'] ?? false)
            ->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1));

        if (isset($config['options'])) {
            $field->options($config['options']);
        }

        if (isset($config['relationship'])) {
            $rel = $config['relationship'];
            $relationship = $rel[0];
            $titleAttribute = $rel[1];
            $modifyQueryUsing = $rel[2] ?? null;

            $field->relationship($relationship, $titleAttribute, $modifyQueryUsing)
                ->preload($config['preload'] ?? true);
        }

        if (array_key_exists('required', $config)) {
            $field->required($config['required']);
        }

        if (array_key_exists('default', $config)) {
            $field->default($config['default']);
        }

        if (array_key_exists('placeholder', $config)) {
            $field->placeholder($config['placeholder']);
        }

        if (array_key_exists('helperText', $config)) {
            $field->helperText($config['helperText']);
        }

        if (array_key_exists('disabled', $config)) {
            $field->disabled($config['disabled']);
        }

        return $field;
    }
}
