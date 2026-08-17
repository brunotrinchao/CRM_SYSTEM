<?php

namespace Tests\Feature;

use App\Models\ProductPhoto;
use Tests\TestCase;

class ProductPhotoUrlCleaningTest extends TestCase
{
    public function test_clean_image_url_removes_local_storage_prefix_from_remote_urls(): void
    {
        $rawCloudinaryUrl = 'https://res.cloudinary.com/dpeaqezkb/image/upload/v1786842917/CRM/j8pmggwojrimxzgs2lil.png?expires=1786845599&signature=15c6cecb734b841b2538522c6f3b5ec1209ce40205496e5d7ffcdaac7289911b';

        // 1. URL limpa direta
        $cleaned1 = ProductPhoto::cleanImageUrl($rawCloudinaryUrl);
        $this->assertEquals($rawCloudinaryUrl, $cleaned1);

        // 2. URL envelopada com prefixo local acidental
        $wrappedUrl = 'http://127.0.0.1:8000/storage/https%3A//res.cloudinary.com/dpeaqezkb/image/upload/v1786842917/CRM/j8pmggwojrimxzgs2lil.png?expires=1786845599&signature=15c6cecb734b841b2538522c6f3b5ec1209ce40205496e5d7ffcdaac7289911b';
        $cleaned2 = ProductPhoto::cleanImageUrl($wrappedUrl);
        $this->assertEquals($rawCloudinaryUrl, $cleaned2);

        // 3. Caminho relativo local
        $relativePath = 'products/sample.jpg';
        $cleaned3 = ProductPhoto::cleanImageUrl($relativePath);
        $this->assertStringContainsString('storage/products/sample.jpg', $cleaned3);
        $this->assertStringStartsWith('http', $cleaned3);
    }
}
