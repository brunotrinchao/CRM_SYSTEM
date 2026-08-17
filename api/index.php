<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

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

// Direct logs to stderr so exceptions are visible in Vercel Function Logs
putenv("LOG_CHANNEL=stderr");

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath($tmpStorage);

$app->handleRequest(Request::capture());
