<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

try {
    /*
    |--------------------------------------------------------------------------
    | Diretórios temporários da Vercel
    |--------------------------------------------------------------------------
    |
    | A Vercel possui filesystem somente leitura, exceto /tmp.
    |
    */

    $tmpStorage = '/tmp/storage';
    $tmpBootstrapCache = '/tmp/bootstrap/cache';

    $directories = [
        $tmpStorage,
        $tmpStorage . '/app',
        $tmpStorage . '/framework',
        $tmpStorage . '/framework/cache',
        $tmpStorage . '/framework/sessions',
        $tmpStorage . '/framework/views',
        $tmpStorage . '/logs',
        $tmpBootstrapCache,
    ];

    foreach ($directories as $directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Variáveis de ambiente
    |--------------------------------------------------------------------------
    */

    putenv("APP_STORAGE={$tmpStorage}");

    putenv("VIEW_COMPILED_PATH={$tmpStorage}/framework/views");

    putenv("APP_CONFIG_CACHE={$tmpBootstrapCache}/config.php");
    putenv("APP_EVENTS_CACHE={$tmpBootstrapCache}/events.php");
    putenv("APP_PACKAGES_CACHE={$tmpBootstrapCache}/packages.php");
    putenv("APP_SERVICES_CACHE={$tmpBootstrapCache}/services.php");

    putenv('LOG_CHANNEL=stderr');

    /*
    |--------------------------------------------------------------------------
    | Laravel
    |--------------------------------------------------------------------------
    */

    $basePath = dirname(__DIR__);

    /*
    |--------------------------------------------------------------------------
    | Composer
    |--------------------------------------------------------------------------
    */

    $autoload = $basePath . '/vendor/autoload.php';

    if (!file_exists($autoload)) {
        throw new RuntimeException(
            'vendor/autoload.php não encontrado. Verifique o build da Vercel.'
        );
    }

    require $autoload;

    /*
    |--------------------------------------------------------------------------
    | Bootstrap
    |--------------------------------------------------------------------------
    */

    $bootstrap = $basePath . '/bootstrap/app.php';

    if (!file_exists($bootstrap)) {
        throw new RuntimeException(
            'bootstrap/app.php não encontrado.'
        );
    }

    $app = require $bootstrap;

    /*
    |--------------------------------------------------------------------------
    | Storage temporário
    |--------------------------------------------------------------------------
    */

    $app->useStoragePath($tmpStorage);

    /*
    |--------------------------------------------------------------------------
    | Processa a requisição
    |--------------------------------------------------------------------------
    */

    $app->handleRequest(
        Request::capture()
    );

} catch (Throwable $e) {

    http_response_code(500);

    header('Content-Type: text/plain; charset=utf-8');

    echo "Erro ao iniciar Laravel na Vercel\n\n";

    echo $e->getMessage() . "\n\n";

    echo "Arquivo: "
        . $e->getFile()
        . ':'
        . $e->getLine()
        . "\n\n";

    echo $e->getTraceAsString();
}