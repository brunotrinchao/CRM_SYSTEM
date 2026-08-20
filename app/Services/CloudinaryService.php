<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CloudinaryService
{
    public static function upload(TemporaryUploadedFile $file): string
    {
        $path = null;

        // 1. Tenta obter o realpath/path nativo se for um arquivo válido no disco
        foreach ([$file->getRealPath(), $file->path()] as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
                $path = $candidate;
                break;
            }
        }

        // 2. Se não resolver direto, busca nos caminhos possíveis do Laravel storage
        if (!$path) {
            $pathname = ltrim($file->getPathname(), '/');
            $basename = basename($pathname);

            $candidates = [
                storage_path('app/' . $pathname),
                storage_path('app/private/' . $pathname),
                storage_path('app/public/' . $pathname),
                storage_path('app/livewire-tmp/' . $basename),
                storage_path('app/private/livewire-tmp/' . $basename),
                storage_path('app/public/livewire-tmp/' . $basename),
                storage_path('app/livewire-tmp/' . $pathname),
                storage_path('app/private/livewire-tmp/' . $pathname),
                storage_path('app/public/livewire-tmp/' . $pathname),
                sys_get_temp_dir() . '/' . $basename,
            ];

            foreach ($candidates as $cand) {
                if (is_string($cand) && $cand !== '' && is_file($cand)) {
                    $path = $cand;
                    break;
                }
            }
        }

        if (!$path || !is_file($path)) {
            throw new \RuntimeException("Não foi possível localizar o arquivo temporário enviado para upload.");
        }

        $result = static::api()->upload(
            $path,
            ['folder' => (string) config('cloudinary.folder')],
        );

        return (string) $result['secure_url'];
    }

    public static function delete(string $url): void
    {
        $publicId = static::publicIdFromUrl($url);

        if ($publicId === null) {
            return;
        }

        try {
            static::api()->destroy($publicId);
        } catch (\Throwable) {
            // se a exclusão falhar (ex.: asset inexistente), não derruba o fluxo
        }
    }

    private static function api(): UploadApi
    {
        // $cloudinaryUrl = getenv('CLOUDINARY_URL') ?: env('CLOUDINARY_URL');

        // if (empty($cloudinaryUrl)) {
            $cloudName = config('cloudinary.cloud_name') ?: env('CLOUDINARY_CLOUD_NAME') ?: env('CLOUDINARY_NAME');
            $apiKey = config('cloudinary.api_key') ?: env('CLOUDINARY_API_KEY');
            $apiSecret = config('cloudinary.api_secret') ?: env('CLOUDINARY_API_SECRET');

            if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
                throw new \RuntimeException(
                    'As credenciais do Cloudinary não foram encontradas. Defina CLOUDINARY_URL ou CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY e CLOUDINARY_API_SECRET no seu .env.'
                );
            }

            // Monta a URL padrão que o SDK do Cloudinary exige internamente
            $cloudinaryUrl = "cloudinary://{$apiKey}:{$apiSecret}@{$cloudName}";
        // }

        // Inicializa a configuração de forma 100% compatível com o SDK oficial
        $config = Configuration::instance($cloudinaryUrl);

        return new UploadApi($config);
    }

    /**
     * Extrai o public_id de uma secure_url.
     *
     * Ex.: .../image/upload/v1/products/imkj2u3il.png -> products/imkj2u3il
     * (public_id inclui a pasta; a extensão é removida)
     */
    private static function publicIdFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return null;
        }

        // corta até /upload/v1/ e remove a extensão
        $marker = '/image/upload/';
        $pos = strpos($path, $marker);

        if ($pos !== false) {
            $path = substr($path, $pos + strlen($marker));
        }

        // remove segmento de versão (v123456) se presente
        $segments = array_values(array_filter(explode('/', $path)));

        if (isset($segments[0]) && str_starts_with($segments[0], 'v')) {
            array_shift($segments);
        }

        $publicId = implode('/', $segments);
        $publicId = preg_replace('/\.\w+$/', '', $publicId);

        return $publicId === '' ? null : $publicId;
    }
}
