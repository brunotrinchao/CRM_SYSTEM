<?php

// Forward Vercel Serverless requests to Laravel's public/index.php
// Ensure /tmp writable directories exist for Laravel storage and cache in serverless environment

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

putenv("VIEW_COMPILED_PATH={$tmpStorage}/framework/views");
putenv("APP_CONFIG_CACHE={$tmpBootstrapCache}/config.php");
putenv("APP_SERVICES_CACHE={$tmpBootstrapCache}/services.php");
putenv("APP_PACKAGES_CACHE={$tmpBootstrapCache}/packages.php");

require __DIR__ . '/../public/index.php';
