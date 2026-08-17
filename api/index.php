<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

try {
    // Forward Vercel Serverless requests to Laravel
    $tmpStorage = '/tmp/storage';
    $tmpBootstrapCache = '/tmp/bootstrap/cache';

    $directories = [
        $tmpStorage . '/framework/views',
        $tmpStorage . '/framework/sessions',
        $tmpStorage . '/framework/cache',
        $tmpStorage . '/logs',
        $tmpBootstrapCache,
    ];

    foreach ($directories as $directory) {
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }
    }

    putenv("APP_STORAGE={$tmpStorage}");
    putenv("VIEW_COMPILED_PATH={$tmpStorage}/framework/views");
    putenv("APP_CONFIG_CACHE={$tmpBootstrapCache}/config.php");
    putenv("APP_SERVICES_CACHE={$tmpBootstrapCache}/services.php");
    putenv("APP_PACKAGES_CACHE={$tmpBootstrapCache}/packages.php");
    putenv("LOG_CHANNEL=stderr");

    define('LARAVEL_START', microtime(true));

    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require __DIR__ . '/../vendor/autoload.php';
    } elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';
    } else {
        throw new \Exception('vendor/autoload.php não encontrado no ambiente Vercel Lambda.');
    }

    $bootstrapPath = file_exists(__DIR__ . '/../bootstrap/app.php')
        ? __DIR__ . '/../bootstrap/app.php'
        : __DIR__ . '/bootstrap/app.php';

    /** @var Application $app */
    $app = require_once $bootstrapPath;

    $app->useStoragePath($tmpStorage);

    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<div style="font-family: sans-serif; padding: 24px; background: #fff1f2; color: #9f1239; border: 1px solid #f43f5e; border-radius: 8px; margin: 20px;">';
    echo '<h2 style="margin-top:0;">⚠️ Erro de Inicialização no Vercel (Laravel)</h2>';
    echo '<p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<h3>Stack Trace:</h3>';
    echo '<pre style="background: #ffe4e6; padding: 12px; border-radius: 4px; overflow-x: auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
    exit(1);
}
