<?php

namespace App\Components\Form;

use App\Services\CepService;
use BackedEnum;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexTextInput;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\PhoneField;
use Filament\Forms\Components\Field;
use Illuminate\Support\Str;

class TextInput
{
    /**
     * Máscaras pré-definidas (BR).
     */
    private const MASKS = [
        'cep' => '99999-999',
        'cpf' => '999.999.999-99',
        'cnpj' => '99.999.999/9999-99',
        'phone' => '(99) 99999-9999',
        'telephone' => '(99) 9999-9999',
    ];

    /**
     * Input de texto rico (FlexTextInput do plugin).
     * Se type for 'phone'|'tel', usa PhoneField do plugin.
     *
     * Config suportada:
     * - required: bool
     * - maxLength: int
     * - default: mixed
     * - helperText: string
     * - placeholder: string
     * - disabled: bool
     * - prefixIcon: BackedEnum|string
     * - suffixIcon: BackedEnum|string
     * - prefix: string
     * - columnSpan: int|string
     * - columnSpanFull: bool
     * - speechDictation: bool (default true)
     * - speechDictationLanguage: string (default 'pt-BR')
     * - emojiPicker: bool (default true)
     * - clearable: bool (default true)
     * - type: string (password, email, tel|phone, ...)
     * - defaultCountry: string (PhoneField, default 'BR')
     * - mask: string (preset 'cep'|'cpf'|'cnpj'|'phone'|'telephone' ou máscara raw ex '999.999.999-99')
     * - cepAutoFill: array (mapa campo→campo; busca ViaCEP ao completar e preenche. Ex: ['street' => 'street', 'city' => 'city'])
     * - numeric / integer / email / password / url / tel: bool (só FlexTextInput)
     */
    public static function make(string $name, ?string $label = null, array $config = []): Field
    {
        $type = $config['type'] ?? null;
        $isPhone = in_array($type, ['phone', 'tel'], true);

        if ($isPhone) {
            $field = PhoneField::make($name)
            ->placeholder('Número de telefone')
                ->defaultCountry($config['defaultCountry'] ?? 'BR');
        } else {
            $field = FlexTextInput::make($name);
        }

        $field->label($label ?? Str::title(str_replace('_', ' ', $name)))
        ->size('lg')
            ->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1));

        // Apenas FlexTextInput tem esses métodos (PhoneField estende Field)
        // if (!$isPhone) {
        //     $field
        //         ->speechDictation($config['speechDictation'] ?? true)
        //         ->speechDictationLanguage($config['speechDictationLanguage'] ?? 'pt-BR')
        //         ->emojiPicker($config['emojiPicker'] ?? true)
        //         ->clearable($config['clearable'] ?? true);
        // }

        if ($config['maxLength'] ?? null) {
            if ($isPhone) {
                // PhoneField estende Field (sem maxLength) — usa regra de validação
                $field->rule('max:' . $config['maxLength']);
            } else {
                $field->maxLength($config['maxLength']);
            }
        }

        if (array_key_exists('required', $config)) {
            $field->required($config['required']);
        }

        if (array_key_exists('default', $config)) {
            $field->default($config['default']);
        }

        if (array_key_exists('helperText', $config)) {
            $field->helperText($config['helperText']);
        }

        if (array_key_exists('placeholder', $config)) {
            $field->placeholder($config['placeholder']);
        }

        if (array_key_exists('disabled', $config)) {
            $field->disabled($config['disabled']);
        }

        if (array_key_exists('hidden', $config)) {
            $field->hidden($config['hidden']);
        }

        // Métodos exclusivos do FlexTextInput (TextInput base)
        if (!$isPhone) {
            if ($type) {
                $field->type($type);
            }

            if ($config['mask'] ?? null) {
                $mask = self::MASKS[$config['mask']] ?? $config['mask'];
                $field->mask($mask);
            }

            // Busca CEP automaticamente ao completar 8 dígitos e preenche campos
            // Ex: 'cepAutoFill' => ['street' => 'street', 'neighborhood' => 'neighborhood', 'city' => 'city', 'state' => 'state']
            if ($config['cepAutoFill'] ?? null) {
                $field->live()
                    ->afterStateUpdated(function ($state, callable $set) use ($config) {
                        $address = CepService::fetch($state ?? '');

                        if (! $address) {
                            return;
                        }

                        foreach ($config['cepAutoFill'] as $source => $target) {
                            if ($target) {
                                $set($target, $address[$target] ?? null);
                            }
                        }
                    });
            }

            if ($config['prefixIcon'] ?? null) {
                $field->prefixIcon($config['prefixIcon']);
            }

            if ($config['prefix'] ?? null) {
                $field->prefix($config['prefix']);
            }
        }

        // PhoneField tem suffixIcon
        if ($config['suffixIcon'] ?? null) {
            $field->suffixIcon($config['suffixIcon']);
        }

        // Métodos exclusivos do TextInput (base do FlexTextInput) — PhoneField não tem
        if (!$isPhone) {
            if ($config['numeric'] ?? false) {
                $field->numeric();
            }

            if ($config['integer'] ?? false) {
                $field->integer();
            }

            if ($config['email'] ?? false) {
                $field->email();
            }

            if ($config['password'] ?? false) {
                $field->password();
            }

            if ($config['url'] ?? false) {
                $field->url();
            }

            if ($config['tel'] ?? false) {
                $field->tel();
            }
        }

        return $field;
    }
}
