<?php

namespace App\Providers\Filament;

use App\Filament\Widgets;
use Bjanczak\FilamentFlexFields\FilamentFlexFieldsPlugin;
use BokshornIt\FilamentActivityTimeline\ActivityTimelinePlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use LaBoiteACode\FilamentDashboardWidgets\FilamentDashboardWidgetsPlugin;
use Octopy\Filament\Palette\PaletteSwitcherPlugin;
use SolutionForest\FilamentSimpleLightBox\SimpleLightBoxPlugin;
use Zvizvi\FilamentColumnFilters\FilamentColumnFiltersPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        config(['filament-flex-fields.playground.enabled' => false]);
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->profile(isSimple: false)
            ->login()
            ->passwordReset()
            ->defaultThemeMode(ThemeMode::Light)
            ->font('Exo')
            ->colors([
                'primary' => Color::Blue, // Azul corporativo principal dos botões e menus ativos
                'gray' => Color::Slate,   // Tons neutros limpos para fundos e bordas
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'mute' => Color::Neutral,
                'text_card'  => Color::hex('#334155'),
                'google' => Color::hex('#4285F4'),
                'facebook' => Color::hex('#1877F2'),
                'indicacao' => Color::hex('#10B981'),
                'site' => Color::hex('#64748B'),
                'olx' => Color::hex('#601986'),
                'telegram' => Color::hex('#229ED9'),
                'whatsapp' => Color::hex('#25D366'),
                'mercado_livre' => Color::hex('#FFE600'),
                'other' => Color::hex('#94A3B8'),
            ])
            ->brandName('CRM System')
            ->spa()
            ->breadcrumbs(false)
            ->databaseNotifications()
            ->maxContentWidth(Width::Full)
            ->sidebarWidth('15rem')
            ->collapsedSidebarWidth('5rem')
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Widgets\TotalRevenueWidget::class,
                // Widgets\OngoingDealsWidget::class,
                // Widgets\ConversionRateWidget::class,
                // Widgets\CriticalStockWidget::class,
                // Widgets\SalesFunnelWidget::class,
                // Widgets\RevenueEvolutionWidget::class,
                // Widgets\TopSellersWidget::class,
                // Widgets\RecentDealsWidget::class,
                // Widgets\CriticalStockTableWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentFlexFieldsPlugin::make(),
                FilamentDashboardWidgetsPlugin::make(),
                SimpleLightBoxPlugin::make(),
                ActivityTimelinePlugin::make()
                ->registerNavigation(false),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true, // Sets the 'account' link in the panel User Menu (default = true)
                        userMenuLabel: 'Perfil', // Customizes the 'account' link label in the panel User Menu (default = null)
                        shouldRegisterNavigation: false, // Adds a main navigation item for the My Profile page (default = false)
                        navigationGroup: 'Settings', // Sets the navigation group for the My Profile page (default = null)
                        hasAvatars: true, // Enables the avatar upload form component (default = false)
                        slug: 'my-profile' // Sets the slug for the profile page (default = 'my-profile')
                    )
                    ->avatarUploadComponent(fn (\Filament\Forms\Components\FileUpload $fileUpload) =>
                        $fileUpload
                            ->label('Foto de Perfil')
                            ->avatar()
                            ->imageEditor()
                            ->circleCropper()
                            ->saveUploadedFileUsing(fn (\Filament\Forms\Components\FileUpload $component, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => \App\Services\CloudinaryService::upload($file))
                            ->deleteUploadedFileUsing(function (\Filament\Forms\Components\FileUpload $component, ?string $file): ?bool {
                                if ($file) {
                                    \App\Services\CloudinaryService::delete($file);
                                }
                                return true;
                            })
                    )
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<style>.fi-wi-widget { min-height: 250px; display: flex; flex-direction: column; } .fi-wi-widget > * { flex: 1; min-height: 250px; }</style>'
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('@livewire("pending-contacts-header-badge")')
            );
    }
}
