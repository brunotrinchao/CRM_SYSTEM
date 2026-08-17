<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Enums\ClientOrigin;
use App\Enums\UserProfile;
use App\Filament\Actions\SimpleActions;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use App\Filament\Resources\Clients\Schemas\ClientInfolist;
use App\Models\Client;
use App\Services\ClientService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('cellphone')
                    ->label('Celular')
                    ->searchable()
                    ->formatStateUsing(function (?string $state): ?string {
                        if (!$state) {
                            return null;
                        }

                        // Remove tudo o que não for número
                        $phone = preg_replace('/\D/', '', $state);

                        if (str_starts_with($phone, '55') && (strlen($phone) === 12 || strlen($phone) === 13)) {
                            $phone = substr($phone, 2);
                        }

                        // Aplica a máscara dependendo se tem 11 dígitos (celular) ou 10 (fixo/antigo)
                        if (strlen($phone) === 11) {
                            return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $phone);
                        }

                        if (strlen($phone) === 10) {
                            return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $phone);
                        }

                        // Retorna o original caso não se encaixe nos tamanhos esperados
                        return $state;
                    }),
                TextColumn::make('origin')
                    ->label('Origem')
                    ->badge()
                    ->color(fn(ClientOrigin $state): string => $state->color()),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('origin')
                    ->label('Origem')
                    ->options(ClientOrigin::options()),
                TrashedFilter::make()
                    ->visible(fn(): bool => Auth::user()->profile !== UserProfile::USER->value)
            ], layout: FiltersLayout::Modal)
            ->recordUrl(null)
            ->recordAction('custom_view')
            ->recordActions([
                // ViewAction::make(),
                SimpleActions::getViewWithEditAndDelete(
                    width: Width::Large,
                    schemaCallback: fn($schema) => ClientForm::configure($schema),
                    schemaViewCallback: fn(Schema $schema) => ClientInfolist::configure($schema),
                    actionCallback: fn(Model $record, array $data) => ClientService::update($record, $data),
                    model: Client::class,
                    recordName: 'Cliente',
                    modal: false,
                    relations: ['addresses', 'deals.product', 'deals.user', 'deals.notesList'],
                    extraFooterActions: [
                        fn(Client $client) => Action::make('to_whatsapp')
                            ->label('WhatsApp')
                            ->icon(Phosphor::WhatsappLogoThin) // Ou use o ícone que preferir
                            ->color('success')
                            ->url(fn($record): string => 'https://wa.me/' . preg_replace('/\D/', '', $record->cellphone))
                            ->openUrlInNewTab()
                            ->tooltip('Conversar no WhatsApp')
                            ->size(Size::ExtraLarge)

                    ]
                )


            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ]);
    }
}
