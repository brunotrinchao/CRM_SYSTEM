<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImageProxyRouteTest extends TestCase
{
    public function test_image_proxy_returns_binary_image_stream_with_cors(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Http::fake([
            'https://res.cloudinary.com/*' => Http::response('fake-binary-image-data', 200, ['Content-Type' => 'image/png']),
        ]);

        $imageUrl = 'https://res.cloudinary.com/demo/image/upload/v1/test.png';
        $response = $this->get('/image-proxy?url=' . urlencode($imageUrl));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertEquals('fake-binary-image-data', $response->getContent());
    }

    public function test_image_proxy_rejects_invalid_urls(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/image-proxy?url=invalid-url');
        $response->assertStatus(400);
    }
}
