<?php

namespace App\Components;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;

class Card
{
    /**
     * Card de agrupamento para Form ou Infolist.
     *
     * Sempre usa Section — consistente entre form e infolist,
     * com título, ícone e grid de colunas.
     *
     * Uso:
     *   Card::make('Informações', [TextInput::make(...), ...])
     *   Card::make(null, [Text::make(...)])  // sem título
     *
     * Config suportada:
     * - icon: BackedEnum|string
     * - iconColor: string
     * - columns: int (default 1)
     * - description: string
     * - collapsible: bool (default false)
     * - collapsed: bool (default false — inicia recolhido)
     */
    public static function make(?string $title = null, array $fields = [], array $config = []): Component
    {
        $section = Section::make($title)
            ->schema($fields)
            ->columns($config['columns'] ?? 1);

        if ($config['icon'] ?? null) {
            $section->icon($config['icon']);
        }

        if ($config['iconColor'] ?? null) {
            $section->iconColor($config['iconColor']);
        }

        if ($config['description'] ?? null) {
            $section->description($config['description']);
        }

        if ($config['collapsible'] ?? false) {
            $section->collapsible();
        }

        if ($config['collapsed'] ?? false) {
            $section->collapsed();
        }

        return $section;
    }
}
