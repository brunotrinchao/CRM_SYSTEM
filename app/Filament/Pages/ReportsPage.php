<?php

namespace App\Filament\Pages;

use App\Enums\DealStatus;
use App\Enums\UserProfile;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use ToneGabes\Filament\Icons\Enums\Phosphor;
use UnitEnum;

class ReportsPage extends Page
{
    use HasFiltersForm;

    protected static string|\BackedEnum|null $navigationIcon = Phosphor::ChartLineUpDuotone;
    protected static ?string $navigationLabel = 'Relatórios';
    protected static ?string $title = 'Relatórios Gerenciais';
    protected static ?string $slug = 'reports';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.reports-page';

    public string $activeTab = 'vendas';

    public function mount(): void
    {
        $start = Carbon::now()->startOfMonth()->format('d/m/Y');
        $end = Carbon::now()->endOfMonth()->format('d/m/Y');

        if (!is_array($this->filters)) {
            $this->filters = [];
        }

        if (empty($this->filters['period_range'])) {
            $this->filters['period_range'] = "{$start} até {$end}";
        }

        if (\Illuminate\Support\Facades\Auth::user()?->profile === UserProfile::USER) {
            $this->filters['user_id'] = \Illuminate\Support\Facades\Auth::id();
        }

        $this->getFiltersForm()->fill($this->filters);
    }

    public function updatedFilters(): void
    {
        if ($this->persistsFiltersInSession()) {
            session()->put(
                $this->getFiltersSessionKey(),
                $this->filters,
            );
        }

        $this->dispatch('page-filters-updated', filters: $this->filters);
    }

    public function filtersForm(Schema $schema): Schema
    {
        $start = Carbon::now()->startOfMonth()->format('d/m/Y');
        $end = Carbon::now()->endOfMonth()->format('d/m/Y');
        $defaultRange = "{$start} até {$end}";

        return $schema
            ->columns([
                'default' => 1,
                'md' => 3,
            ])
            ->components([
                DateRangePicker::make('period_range')
                    ->label('Período')
                    ->defaultThisMonth()
                    ->default($defaultRange)
                    ->autoApply()
                    ->separator(' até ')
                    ->linkedCalendars()
                    ->live()
                    ->columnSpan(1),

                Select::make('user_id')
                    ->label('Vendedor')
                    ->options(fn () => User::query()
                        ->where('profile', UserProfile::USER)
                        ->pluck('name', 'id')
                        ->toArray())
                    ->placeholder('Todos os Vendedores')
                    ->default(fn () => \Illuminate\Support\Facades\Auth::user()?->profile === UserProfile::USER ? \Illuminate\Support\Facades\Auth::id() : null)
                    ->disabled(fn () => \Illuminate\Support\Facades\Auth::user()?->profile === UserProfile::USER)
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->columnSpan(1),

                Select::make('status')
                    ->label('Status do Negócio')
                    ->options(DealStatus::options())
                    ->placeholder('Todos os Status')
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->columnSpan(1),
            ]);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
}
