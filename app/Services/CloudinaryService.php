<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CloudinaryService
{
    public static function upload(TemporaryUploadedFile $file): string
    {
        $path = $file->getRealPath();

        if (! is_string($path) || $path === '' || ! is_file($path)) {
            // em fluxos Livewire o tmp é um stream; usa o recurso php://temp subjacente
            $path = (string) $file->getPathname();
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
        if (! getenv('CLOUDINARY_URL')) {
            // formato canônico: cloudinary://KEY:SECRET@NAME
            putenv(sprintf(
                'CLOUDINARY_URL=cloudinary://%s:%s@%s',
                (string) config('cloudinary.api_key'),
                (string) config('cloudinary.api_secret'),
                (string) config('cloudinary.cloud_name'),
            ));
        }

        return new UploadApi(Configuration::instance());
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

        if (! is_string($path) || $path === '') {
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
