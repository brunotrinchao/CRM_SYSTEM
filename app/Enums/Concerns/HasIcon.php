<?php

namespace App\Enums\Concerns;

use Filament\Support\Contracts\ScalableIcon;

/**
 * Padroniza o ícone (ScalableIcon) de cada case do enum.
 *
 * Suporta qualquer família de ícones que implemente ScalableIcon:
 * Heroicon, Phosphor, etc.
 *
 * Cada enum deve implementar:
 *   public function icon(): ScalableIcon;
 */
trait HasIcon
{
    abstract public function icon(): ScalableIcon;

    /** @return array<string, ScalableIcon> value => ScalableIcon */
    public static function icons(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $case) => $carry + [$case->value => $case->icon()],
            [],
        );
    }
}
