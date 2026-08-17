<?php

namespace App\Filament\Pages;

use App\Enums\UserProfile;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexDateRangeField;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use App\Filament\Widgets;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Carbon;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    // protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Painel Gerencial';

    // Configuração de quais widgets cada perfil pode ver
    // Adicione widgets aqui para restringir a visualização
    protected array $allowedWidgets = [
        UserProfile::ADMIN->value => [
            Widgets\TotalRevenueWidget::class,
            Widgets\OngoingDealsWidget::class,
            Widgets\ConversionRateWidget::class,
            Widgets\CriticalStockWidget::class,
            Widgets\EqualHeightContainer::class,
        ],
        UserProfile::MANAGER->value => [
            Widgets\TotalRevenueWidget::class,
            Widgets\OngoingDealsWidget::class,
            Widgets\ConversionRateWidget::class,
            Widgets\CriticalStockWidget::class,
            Widgets\EqualHeightContainer::class,
        ],
        UserProfile::USER->value => [
            Widgets\OngoingDealsWidget::class,
            Widgets\PendingContactsStatWidget::class,
            Widgets\EqualHeightContainer::class,
        ],
    ];

    public function getWidgets(): array
    {
        $user = auth()->user();
        $profile = $user->profile->value ?? UserProfile::USER->value;

        // Se o perfil existe na configuração, retorna os widgets permitidos
        if (isset($this->allowedWidgets[$profile])) {
            return $this->allowedWidgets[$profile];
        }

        // Fallback: retorna todos os widgets se o perfil não estiver configurado
        return [
            Widgets\TotalRevenueWidget::class,
            Widgets\OngoingDealsWidget::class,
            Widgets\ConversionRateWidget::class,
            Widgets\CriticalStockWidget::class,
            Widgets\EqualHeightContainer::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 4, // Permite acomodar bem os 4 stats na primeira linha
        ];
    }

    // Método auxiliar para os widgets consultarem o perfil do usuário
    public static function getUserProfile(): string
    {
        return auth()->user()?->profile?->value ?? UserProfile::USER->value;
    }

    // Método auxiliar para os widgets consultarem o ID do usuário (para dados filtrados)
    public static function getUserId(): int
    {
        return auth()->id();
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->schema([
                    DateRangePicker::make('period_range')
                        ->defaultCustom(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth())
                        ->autoApply()
                        ->separator(' até ')
                        ->linkedCalendars(),
                ])
        ];
    }
}