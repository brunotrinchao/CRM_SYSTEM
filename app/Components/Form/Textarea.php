<?php

namespace App\Components\Form;

use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexTextareaField;
use Illuminate\Support\Str;

class Textarea
{
    /**
     * Textarea rico (FlexTextareaField do plugin).
     *
     * Config suportada:
     * - required: bool
     * - maxLength: int
     * - rows: int
     * - footer: string
     * - default: mixed
     * - placeholder: string
     * - disabled: bool
     * - columnSpan: int|string
     * - columnSpanFull: bool (default true)
     * - characterCounter: bool (default true)
     * - animatedAutosize: bool (default true)
     * - speechDictation: bool (default true)
     * - speechDictationLanguage: string (default 'pt-BR')
     * - emojiPicker: bool (default true)
     */
    public static function make(string $name, ?string $label = null, array $config = []): FlexTextareaField
    {
        $field = FlexTextareaField::make($name)
        ->size('lg')
            ->label($label ?? Str::title(str_replace('_', ' ', $name)))
            ->characterCounter($config['characterCounter'] ?? true)
            ->animatedAutosize($config['animatedAutosize'] ?? true)
            ->speechDictation($config['speechDictation'] ?? true)
            ->speechDictationLanguage($config['speechDictationLanguage'] ?? 'pt-BR')
            ->emojiPicker($config['emojiPicker'] ?? true)
            ->columnSpan($config['columnSpanFull'] ?? true ? 'full' : ($config['columnSpan'] ?? 1));

        if ($config['required'] ?? false) {
            $field->required();
        }

        if ($config['maxLength'] ?? null) {
            $field->maxLength($config['maxLength']);
        }

        if ($config['rows'] ?? null) {
            $field->rows($config['rows']);
        }

        if ($config['footer'] ?? null) {
            $field->footer($config['footer']);
        }

        if ($config['default'] ?? null) {
            $field->default($config['default']);
        }

        if ($config['placeholder'] ?? null) {
            $field->placeholder($config['placeholder']);
        }

        if ($config['disabled'] ?? false) {
            $field->disabled();
        }

        return $field;
    }
}
