<?php

namespace App\Filament\Widgets;

use LaBoiteACode\FilamentDashboardWidgets\Data\Funnel;
use LaBoiteACode\FilamentDashboardWidgets\Data\FunnelStage;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\FunnelWidget;

class SalesFunnelWidget extends FunnelWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStages(): array
    {
        return [
            FunnelStage::make('Visitors', 4_200),
            FunnelStage::make('Signups', 1_280),
            FunnelStage::make('Activated', 640),
            FunnelStage::make('Paying', 210)->color('success'),
        ];
    }


}