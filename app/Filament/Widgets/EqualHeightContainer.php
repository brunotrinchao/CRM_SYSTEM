<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class EqualHeightContainer extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.equal-height-container';

    protected int | string | array $columnSpan = 'full';
}
