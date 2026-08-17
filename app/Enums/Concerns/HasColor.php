<?php

namespace App\Enums\Concerns;

/**
 * Padroniza a cor (Filament) de cada case do enum.
 *
 * Cada enum deve implementar:
 *   public function color(): string;
 */
trait HasColor
{
    abstract public function color(): string;

    /** @return array<string, string> value => color */
    public static function colors(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $case) => $carry + [$case->value => $case->color()],
            [],
        );
    }
}
