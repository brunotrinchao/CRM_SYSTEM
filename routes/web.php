<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/image-proxy', function (Request $request) {
    $url = urldecode((string) $request->query('url'));

    if (! $url || ! str_starts_with($url, 'http')) {
        abort(400, 'Invalid image URL');
    }

    try {
        $response = Http::timeout(10)->get($url);

        if (! $response->successful()) {
            abort(404, 'Image not found');
        }

        $contentType = $response->header('Content-Type') ?? 'image/png';
        if ($contentType === 'application/octet-stream') {
            $contentType = 'image/png';
        }

        return response($response->body(), 200, [
            'Content-Type' => $contentType,
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    } catch (\Throwable $e) {
        abort(500, 'Error proxying image');
    }
})->middleware('web');
