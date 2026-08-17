<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CepService
{
    /**
     * Busca CEP na ViaCEP e retorna dados normalizados.
     *
     * @return array{cep: string, street: string, neighborhood: string, city: string, state: string}|null
     */
    public static function fetch(string $cep): ?array
    {
        $digits = preg_replace('/\D/', '', $cep);

        if (strlen($digits) !== 8) {
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->get("https://viacep.com.br/ws/{$digits}/json/");

            if ($response->failed() || $response->json('erro')) {
                return null;
            }

            return [
                'cep' => $response->json('cep'),
                'street' => $response->json('logradouro') ?: null,
                'neighborhood' => $response->json('bairro') ?: null,
                'city' => $response->json('localidade') ?: null,
                'state' => $response->json('uf') ?: null,
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
