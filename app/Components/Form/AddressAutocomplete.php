<?php

namespace App\Components\Form;

use Bjanczak\FilamentFlexFields\Filament\Forms\Components\AddressAutocompleteField;
use Filament\Forms\Components\Field;
use Illuminate\Support\Str;

class AddressAutocomplete
{
    /**
     * AddressAutocompleteField do plugin (geocoding Mapbox).
     *
     * Requer MAPBOX_ACCESS_TOKEN no .env.
     * Armazena objeto estruturado no campo (storeFormat 'structured').
     *
     * Uso:
     *   AddressAutocomplete::make('address', 'Endereço', [
     *       'searchTypes' => ['address', 'place'],
     *       'language' => 'pt-BR',
     *       'countries' => ['BR'],
     *   ])
     *
     * Config suportada:
     * - searchable: bool (default true)
     * - prefixIcon / clearIcon: BackedEnum|string
     * - storeFormat: 'structured'|'string' (default 'structured')
     * - stringFormat: string (ex '{place_name}') p/ storeFormat string
     * - requiredFields: array (keys obrigatórias do state)
     * - countries: array (ex ['BR'])
     * - searchTypes: array (ex ['address', 'place', 'locality'])
     * - streetAddressesOnly: bool
     * - language: string (ex 'pt-BR')
     * - minSearchLength: int (default 2)
     * - searchDebounce: int ms (default 350)
     * - mapboxToken: string (override do token config)
     * - columnSpan / columnSpanFull: layout no form pai
     */
    public static function make(string $name, ?string $label = null, array $config = []): Field
    {
        $field = AddressAutocompleteField::make($name)
            ->label($label ?? Str::title(str_replace('_', ' ', $name)));

        if (($config['searchable'] ?? true) === false) {
            $field->searchable(false);
        }

        if ($config['prefixIcon'] ?? null) {
            $field->prefixIcon($config['prefixIcon']);
        }

        if ($config['clearIcon'] ?? null) {
            $field->clearIcon($config['clearIcon']);
        }

        if ($config['storeFormat'] ?? null) {
            $field->storeFormat($config['storeFormat']);
        }

        if ($config['stringFormat'] ?? null) {
            $field->stringFormat($config['stringFormat']);
        }

        if ($config['requiredFields'] ?? null) {
            $field->requiredFields($config['requiredFields']);
        }

        if ($config['countries'] ?? null) {
            $field->countries($config['countries']);
        }

        if ($config['searchTypes'] ?? null) {
            $field->searchTypes($config['searchTypes']);
        }

        if ($config['streetAddressesOnly'] ?? false) {
            $field->streetAddressesOnly();
        }

        if ($config['language'] ?? null) {
            $field->language($config['language']);
        }

        if ($config['minSearchLength'] ?? null) {
            $field->minSearchLength($config['minSearchLength']);
        }

        if ($config['searchDebounce'] ?? null) {
            $field->searchDebounce($config['searchDebounce']);
        }

        if ($config['mapboxToken'] ?? null) {
            $field->mapboxToken($config['mapboxToken']);
        }

        if ($config['required'] ?? false) {
            $field->required();
        }

        $field->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 1));

        return $field;
    }
}
