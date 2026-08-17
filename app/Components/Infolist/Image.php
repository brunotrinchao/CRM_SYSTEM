<?php

namespace App\Components\Infolist;

use Filament\Infolists\Components\ImageEntry;

class Image
{
    /**
     * ImageEntry (imagem) — Infolist.
     *
     * Uso:
     *   Image::make('image', 'Foto', ['height' => 200])
     *
     * Config suportada:
     * - height: int|string
     * - width: int|string
     * - circular: bool (default false)
     * - square: bool (default false)
     * - size: int|string (tamanho quadrado)
     * - defaultImageUrl: string (placeholder)
     */
    public static function make(string $name, ?string $label = null, array $config = []): ImageEntry
    {
        $entry = ImageEntry::make($name);

        if ($label) {
            $entry->label($label);
        } else {
            $entry->hiddenLabel();
        }

        if ($config['size'] ?? null) {
            $entry->size($config['size']);
        } else {
            if ($config['height'] ?? null) {
                $entry->height($config['height']);
            }
            if ($config['width'] ?? null) {
                $entry->width($config['width']);
            }
        }

        if ($config['circular'] ?? false) {
            $entry->circular();
        }

        if ($config['square'] ?? false) {
            $entry->square();
        }

        if ($config['defaultImageUrl'] ?? null) {
            $entry->defaultImageUrl($config['defaultImageUrl']);
        }

        return $entry;
    }
}
