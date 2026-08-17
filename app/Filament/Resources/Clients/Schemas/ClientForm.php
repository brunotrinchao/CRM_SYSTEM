<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Enums\AddressType;
use App\Enums\ClientOrigin;
use App\Components\Card;
use App\Components\Form\Select;
use App\Components\Form\Textarea;
use App\Components\Form\TextInput;
use App\Components\Repeater;
use App\Models\Client;
use App\Services\ClientService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                    TextInput::make('name', 'Nome', [
                        'required' => true,
                        'maxLength' => 255,
                        'prefixIcon' => Heroicon::User,
                    ]),
                    TextInput::make('email', 'E-mail', [
                        'type' => 'email',
                        'maxLength' => 255,
                        'prefixIcon' => Heroicon::Envelope,
                    ])
                    ->unique(
                        table: 'clients', 
                        column: 'email', 
                        ignoreRecord: true
                    ),
                    TextInput::make('phone', 'Telefone', [
                        'type' => 'tel',
                        'maxLength' => 255,
                        'prefixIcon' => Heroicon::Phone,
                    ]),
                    TextInput::make('cellphone', 'Celular', [
                        'type' => 'tel',
                        'maxLength' => 255,
                        'prefixIcon' => Heroicon::DevicePhoneMobile,
                    ]),
                    Select::make('origin', 'Origem', [
                        'options' => ClientOrigin::options(),
                        'required' => true,
                    ]),
                    Textarea::make('description', 'Descrição', [
                        'maxLength' => 65535,
                    ]),
                Repeater::make('addresses', 'Endereços', [
                    TextInput::make('zip_code', 'CEP', [
                        'required' => true,
                        'mask' => 'cep',
                        'cepAutoFill' => [
                            'street',
                            'neighborhood',
                            'city',
                            'state'
                        ],
                        'columnSpanFull' => false,
                    ]),
                    Select::make('type', 'Tipo', [
                        'required' => true,
                        'default' => AddressType::RESIDENCE->value,
                        'options' => AddressType::options(),
                        'columnSpanFull' => false,
                        'native' => true,
                        'searchable' => false
                    ]),
                    TextInput::make('street', 'Rua / Logradouro', [
                        'required' => true,
                        'maxLength' => 255,
                        'columnSpanFull' => true,
                    ]),
                    TextInput::make('number', 'Número', [
                        'numeric' => true,
                    ]),
                    TextInput::make('neighborhood', 'Bairro', [
                        'required' => true,
                        'maxLength' => 255,
                    ]),
                    TextInput::make('city', 'Cidade', [
                        'required' => true,
                        'maxLength' => 255,
                    ]),
                    Select::make('state', 'Estado', [
                        'required' => true,
                        'options' => [
                            'AC' => 'Acre',
                            'AL' => 'Alagoas',
                            'AP' => 'Amapá',
                            'AM' => 'Amazonas',
                            'BA' => 'Bahia',
                            'CE' => 'Ceará',
                            'DF' => 'Distrito Federal',
                            'ES' => 'Espírito Santo',
                            'GO' => 'Goiás',
                            'MA' => 'Maranhão',
                            'MT' => 'Mato Grosso',
                            'MS' => 'Mato Grosso do Sul',
                            'MG' => 'Minas Gerais',
                            'PA' => 'Pará',
                            'PB' => 'Paraíba',
                            'PR' => 'Paraná',
                            'PE' => 'Pernambuco',
                            'PI' => 'Piauí',
                            'RJ' => 'Rio de Janeiro',
                            'RN' => 'Rio Grande do Norte',
                            'RS' => 'Rio Grande do Sul',
                            'RO' => 'Rondônia',
                            'RR' => 'Roraima',
                            'SC' => 'Santa Catarina',
                            'SP' => 'São Paulo',
                            'SE' => 'Sergipe',
                            'TO' => 'Tocantins',
                        ],
                    ]),
                    TextInput::make('reference', 'Ponto de referência',[
                        'maxLength' => 255,
                        'columnSpanFull' => true,
                    ]),
                    Hidden::make('country')
                    ->default('Brasil'),
                ], [
                    'columns' => 2,
                    'collapsible' => true,
                    'collapsed' => true,
                    'reorderable' => false,
                    'addActionLabel' => 'Adicionar endereço',
                    'maxItems' => 5,
                    'defaultItems' => 0
                ])
            ]);
    }
}
