<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Client;
use Filament\Notifications\Notification;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;

class ClientService
{
    public static function create(array $data): ?Client
    {
        try {
            $data['user_id'] = Auth::id();

            $addresses = $data['addresses'] ?? [];
            unset($data['addresses']);

            $data['phone'] = $data['phone']['e164'] ?? null;
            $data['cellphone'] = $data['cellphone']['e164'] ?? null;

            $client = Client::create($data);

            self::syncAddresses($client, $addresses);

            return $client;

        } catch (UniqueConstraintViolationException $e) {
            // Interrompe e avisa o usuário sem quebrar o sistema
            Notification::make()
                ->title('Erro ao cadastrar cliente')
                ->body('Já existe um cliente cadastrado com este e-mail para este usuário/responsável.')
                ->danger()
                ->send();

            // Se estiver em uma Action, você pode usar:
            return null;
        }
    }

    public static function update(Client $client, array $data): Client
    {
        $addresses = $data['addresses'] ?? [];
        unset($data['addresses']);

        $data['phone'] = $data['phone']['e164'] ?? null;
        $data['cellphone'] = $data['cellphone']['e164'] ?? null;

        $client->update($data);

        self::syncAddresses($client, $addresses);

        return $client;
    }

    private static function syncAddresses(Client $client, array $addresses): void
    {
        $allIds = [];

        foreach ($addresses as $address) {
            // Salva ou atualiza cada endereço individualmente aproveitando a função auxiliar
            $savedAddress = self::syncOnlyAddresse($client, $address);

            $allIds[] = $savedAddress->id;
        }

        // Remove endereços que não estão mais na lista enviada
        $client->addresses()->whereNotIn('id', $allIds)->delete();
    }

    public static function syncOnlyAddresse(Client $client, array $address): Address
    {
        $id = $address['id'] ?? null;
        $data = $address;

        // Garante as chaves estrangeiras e do usuário
        $data['user_id'] = Auth::id();
        $data['client_id'] = $client->id;

        // Remove o ID do array de dados para evitar conflito no update/create
        unset($data['id']);

        if ($id) {
            // Busca o registro existente, atualiza e o retorna como objeto Address
            $addressRecord = $client->addresses()->whereKey($id)->firstOrFail();
            $addressRecord->update($data);

            return $addressRecord;
        }

        // Cria um novo endereço e o retorna
        return $client->addresses()->create($data);
    }
}
