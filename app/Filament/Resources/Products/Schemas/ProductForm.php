<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Components\Card;
use App\Components\Form\CurrencyInput;
use App\Components\Form\NumberInput;
use App\Components\Form\Select;
use App\Components\Form\TextInput;
use App\Components\Form\Textarea;
use App\Components\Form\Toggle;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Services\CategoryService;
use App\Services\CloudinaryService;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexImageUpload;
use Filament\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Support\Icons\Heroicon;
use Hamcrest\Core\Set;
use Illuminate\Database\Eloquent\Model;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações do Produto')
                    ->icon(Heroicon::OutlinedCube)
                    ->iconColor(Color::Blue)
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name', 'Nome', [
                                    'required' => true,
                                    'prefixIcon' => Heroicon::Cube,
                                    'columnSpanFull' => true
                                ]),
                                Select::make('category_id', 'Categoria', [
                                    'relationship' => ['category', 'name'],
                                    'required' => true,
                                    'columnSpanFull' => true
                                ])
                                    ->suffixAction(
                                        SimpleActions::getCreateModal(
                                            width: Width::Small,
                                            schemaCallback: fn($schema) => CategoryForm::configure($schema),
                                            actionCallback: fn(array $data) => CategoryService::create($data),
                                            recordName: 'Categoria',
                                            modal: true,
                                            model: \App\Models\Category::class,
                                            name: 'create_category_modal',
                                            afterCreate: function (\Illuminate\Database\Eloquent\Model $record, $livewire) {
                                                SimpleActions::setFieldOnParentForm($livewire, 'category_id', $record->id);
                                            }
                                        ),
                                    ),
                                TextInput::make('sku', 'SKU', [
                                    'columnSpanFull' => false
                                ]),
                            ])
                    ]),
                Section::make('Precificação e Estoque Físico')
                    ->icon(Heroicon::OutlinedCurrencyDollar)
                    ->iconColor(Color::Blue)
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                CurrencyInput::make('price', 'Preço (R$)', [
                                    'required' => true,
                                    'columnSpanFull' => false
                                ]),
                                NumberInput::make('current_stock', 'Estoque Atual', [
                                    'default' => 0,
                                ]),
                                NumberInput::make('minimum_stock', 'Estoque Mínimo', [
                                    'default' => 0,
                                ]),
                            ])
                    ]),
                Section::make('Observações e Especificações')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Textarea::make('observation', 'Observação', [
                            'columnSpanFull' => true,
                        ])
                    ]),
                Section::make('Fotos do Produto')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->iconColor(Color::Blue)
                    ->compact()
                    ->collapsible()
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'isolate relative z-0',
                        'style' => 'isolation: isolate; position: relative; z-index: 0;',
                    ])
                    ->schema([
                        FlexImageUpload::make('photos')
                            ->hiddenLabel()
                            ->extraAttributes([
                                'class' => 'isolate relative z-0',
                                'style' => 'isolation: isolate; position: relative; z-index: 0;',
                            ])
                            ->afterStateHydrated(function ($component, ?Model $record): void {
                                if (!$record) {
                                    return;
                                }

                                // Garante que a relação está carregada
                                $record->loadMissing('photos');

                                // Mapeia as URLs ou caminhos salvos na relação
                                $images = $record->photos
                                    ->map(fn ($photo) => \App\Models\ProductPhoto::cleanImageUrl($photo->image))
                                    ->filter()
                                    ->values()
                                    ->all();

                                $component->state($images);
                            })
                            ->columnSpanFull()
                            ->withRecommendedDefaults()
                            ->imageEditor()
                            ->allowedExtensions(['jpg', 'jpeg', 'png', 'webp'])
                            ->allowWebcamUpload()
                            ->disk(null)
                            ->directory('products')
                            ->maxFiles(3)
                            ->panelLayout('grid')
                            ->imagePreviewHeight('140px')
                            ->reorderable()
                            ->optimizeImages()
                            ->multiple()
                            ->variant('primary')
                            ->columnSpanFull()
                            ->preventFilePathTampering(false)
                            ->fetchFileInformation(false)
                            ->helperText('Arraste ou selecione até 3 fotos em alta qualidade (PNG, JPG ou WEBP).')
                            ->getUploadedFileUsing(function ($component, string $file, string | array | null $storedFileNames): ?array {
                                $url = \App\Models\ProductPhoto::cleanImageUrl($file);

                                return [
                                    'name' => basename(parse_url($file, PHP_URL_PATH) ?? $file),
                                    'size' => 0,
                                    'type' => null,
                                    'url' => $url,
                                ];
                            })
                            ->saveUploadedFileUsing(function ($component, TemporaryUploadedFile $file): string {
                                // Retorna a URL string gerada pelo Cloudinary
                                return CloudinaryService::upload($file);
                            })
                            ->deleteUploadedFileUsing(function ($component, string $file): ?bool {
                                return CloudinaryService::delete($file);
                            })
                    ]),
            ]);
    }
}
