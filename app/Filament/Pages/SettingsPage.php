<?php

namespace App\Filament\Pages;

use App\Components\Form\TextInput;
use App\Enums\UserProfile;
use App\Models\SystemSetting;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexImageUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class SettingsPage extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|\BackedEnum|null $navigationIcon = Phosphor::GearDuotone;

    protected static \UnitEnum|string|null $navigationGroup = 'Configurações';

    protected static ?string $navigationLabel = 'Configurações do Sistema';

    protected static ?string $title = 'Configurações do Sistema';

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.settings-page';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->profile === UserProfile::ADMIN;
    }

    public function mount(): void
    {
        $setting = SystemSetting::getSetting();

        $this->form->fill([
            'company_name' => $setting->company_name,
            'company_logo' => $setting->company_logo,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name', 'Nome da Empresa', [
                    'required' => true,
                    'placeholder' => 'Digite o nome da empresa',
                ]),
                FlexImageUpload::make('company_logo', 'Logo da Empresa')
                    ->disk('public')
                    ->directory('settings')
                    ->allowedExtensions(['jpg', 'jpeg', 'png', 'webp', 'svg'])
                    ->imagePreviewHeight('100px'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = SystemSetting::getSetting();
        $setting->update([
            'company_name' => $data['company_name'] ?? $setting->company_name,
            'company_logo' => is_array($data['company_logo']) ? ($data['company_logo'][0] ?? null) : ($data['company_logo'] ?? null),
        ]);

        Notification::make()
            ->title('Configurações salvas com sucesso!')
            ->success()
            ->send();
    }
}
