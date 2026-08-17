<?php

namespace App\Filament\Resources\Deals\Schemas;

use App\Components\Form\CurrencyInput;
use App\Components\Form\Select;
use App\Components\Form\Textarea;
use App\Components\Form\Toggle;
use App\Components\Infolist\Text;
use App\Models\Client;
use App\Models\User;
use Bjanczak\FilamentFlexFields\Enums\ControlSize;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexTextareaField;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\FlexTextInput;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\SwitchField;
use Bjanczak\FilamentFlexFields\Filament\Forms\Components\UserSelect;
use Filament\Forms\Components\Hidden;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DealDiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('total_value'),
                Callout::make('')
                ->description(function (Get $get) {
                    $total = (float) ($get('total_value') ?? 0);
                    $isPercentage = (bool) $get('type');
                    
                    $amount = (float) ($get('amount') ?? 0);
                    if (!$isPercentage) {
                        $amount = $amount / 100; // Ajuste caso venha em centavos
                    }

                    $discountValue = $isPercentage ? ($total * ($amount / 100)) : $amount;
                    $finalValue = max(0, $total - $discountValue);

                    $discount = $amount > 0 ? '(desconto de ' . ($isPercentage ? $amount . '%' : 'R$ ' .number_format($amount, 2, ',', '.')) . ')' : null;

                    return new HtmlString(
                        'Informe o motivo e o valor do desconto desejado para aprovação.<br/><br/>' .
                        '<small>Valor total do negócio: <span style="font-family: \'Space Mono\', monospace;"><b>R$ ' . number_format($total, 2, ',', '.') . '</b></span></small><br/>' .
                        '<small>Valor com desconto: <span style="font-family: \'Space Mono\', monospace; color: #059669;"><b>R$ ' . number_format($finalValue, 2, ',', '.') . '</b> '.$discount.'</span></small>'
                    );
                })
                ->info(),
                UserSelect::make('reviewed_by')
                ->required()
                ->label('Solicitar para')
                ->options(
                    User::query()
                        ->whereIn('profile', ['ADMIN', 'MANAGER'])
                        ->get()
                        ->mapWithKeys(fn (User $user) => [
                            $user->id => [
                                $user->id => [
                                    'label' => $user->name, // Alterado de 'name' para 'label'
                                    'description' => $user->email, // Opcional: exibe o e-mail abaixo do nome
                                ]
                            ]
                        ])
                        ->toArray()
                )
                ->size(ControlSize::Md),
                Toggle::make('type')
                    ->label('Tipo de Desconto')
                    ->inline(false)
                    ->onColor('primary')
                    ->offColor('primary')
                    ->onIcon(Phosphor::Percent)
                    ->offIcon(Phosphor::CurrencyDollar)
                    ->size(ControlSize::Lg)
                    ->default(true)
                    ->live(onBlur: false, debounce: 300) // Torna o campo reativo para atualizar os demais
                    ->afterStateUpdated(function (Set $set) {
                        // Limpa o valor ao alternar para evitar dados inconsistentes
                        $set('amount', null);
                    }),

                // Porcentagem: campo fracionado (0–100), visível quando type = true
                FlexTextInput::make('amount')
                    ->label('Porcentagem (%)')
                    ->live()
                    ->numeric(true)
                    ->step('0.01')
                    ->minValue(0)
                    ->maxValue(100)
                    ->size(ControlSize::Lg)
                    ->suffix('%')
                    ->hidden(fn(Get $get) => ! (bool) $get('type'))
                    ->dehydratedWhenHidden(false)
                    ->required(fn(Get $get) => (bool) $get('type')),

                // Valor monetário: currency mask (R$), visível quando type = false
                CurrencyInput::make('amount', 'Valor (R$)', ['required' => true])
                    ->hidden(fn(Get $get) => (bool) $get('type'))
                    ->dehydratedWhenHidden(false),

                Textarea::make('reason')
                    ->label('Motivo da solicitação')
                    ->placeholder('Descreva o motivo desta alteração...')
                    ->rows(3),
            ]);
    }

}
