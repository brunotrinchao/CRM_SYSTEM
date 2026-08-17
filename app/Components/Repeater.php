<?php

namespace App\Components;

use Filament\Forms\Components\Repeater as FilamentRepeater;
use Illuminate\Support\Facades\Auth;

class Repeater
{
    /**
     * Repeater de agrupamento (relacionamentos 1:N) — Form.
     *
     * Uso:
     *   Repeater::make('addresses', 'Endereços', [
     *       TextInput::make('street', 'Rua'),
     *       TextInput::make('zip_code', 'CEP', ['mask' => 'cep']),
     *   ], [
     *       'relationship' => 'addresses',
     *       'columns' => 2,
     *   ])
     *
     * Config suportada:
     * - relationship: string (usa relacionamento do model)
     * - columns: int (columns interno, default 1)
     * - grid: int (grid interno, default 1)
     * - defaultItems: int (default 1; use 0 p/ iniciar vazio)
     * - minItems / maxItems: int
     * - addActionLabel: string
     * - collapsible: bool (default false)
     * - collapsed: bool (default false)
     * - reorderable / cloneable: bool (default true)
     * - columnSpan / columnSpanFull: layout no form pai
     */
    public static function make(string $name, ?string $label = null, array $fields = [], array $config = []): FilamentRepeater
    {
        $repeater = FilamentRepeater::make($name)
            ->label($label)
            ->schema($fields)
            ->grid($config['grid'] ?? 1)
            ->compact()
            ->columns($config['columns'] ?? 1)
            ->itemLabel(function (array $state) use ($config): ?string {
                // config itemLabel: string (campo) ou callable (personalizado)
                if (isset($config['itemLabel'])) {
                    $itemLabel = $config['itemLabel'];

                    if (is_string($itemLabel) && isset($state[$itemLabel]) && filled($state[$itemLabel])) {
                        return (string) $state[$itemLabel];
                    }

                    if (is_callable($itemLabel)) {
                        return $itemLabel($state);
                    }
                }

                // default: campo de exibição comum antes do id
                foreach (['street', 'name', 'title', 'label'] as $field) {
                    if (isset($state[$field]) && filled($state[$field])) {
                        return (string) $state[$field];
                    }
                }

                // fallback: primeira chave não-id
                foreach ($state as $key => $value) {
                    if ($key === 'id' || $key === 'uuid' || blank($value)) {
                        continue;
                    }

                    return (string) $value;
                }

                return null;
            });

        if ($config['relationship'] ?? null) {
            $repeater->relationship($config['relationship'])
                ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => array_merge($data, ['user_id' => Auth::id()]))
                ->mutateRelationshipDataBeforeSaveUsing(fn (array $data) => array_merge($data, ['user_id' => Auth::id()]));
        }

        if (array_key_exists('defaultItems', $config)) {
            $repeater->defaultItems($config['defaultItems']);
        }

        if ($config['minItems'] ?? null) {
            $repeater->minItems($config['minItems']);
        }

        if ($config['maxItems'] ?? null) {
            $repeater->maxItems($config['maxItems']);
        }

        if ($config['addActionLabel'] ?? null) {
            $repeater->addActionLabel($config['addActionLabel']);
        }

        if ($config['collapsible'] ?? false) {
            $repeater->collapsible();
        }

        if ($config['collapsed'] ?? false) {
            $repeater->collapsed();
        }

        if (($config['reorderable'] ?? true) === false) {
            $repeater->reorderable(false);
        }

        if (($config['cloneable'] ?? true) === false) {
            $repeater->cloneable(false);
        }

        if ($config['required'] ?? false) {
            $repeater->required();
        }

        $repeater->columnSpan($config['columnSpanFull'] ?? false ? 'full' : ($config['columnSpan'] ?? 'full'));

        return $repeater;
    }
}
