<?php

namespace App\Enums\Concerns;

use Filament\Support\Contracts\HasLabel as HasLabelContract;

/**
 * Compatível com Filament\Contracts\HasLabel.
 * Cada enum deve implementar: public function label(): string;
 */
trait HasLabel
{
    /** @return string|null compatível com Filament\Contracts\HasLabel */
    public function getLabel(): ?string
    {
        return $this->label();
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $case) => $carry + [$case->value => $case->label()],
            [],
        );
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $case) => $carry + [$case->value => $case->label()],
            [],
        );
    }
}
