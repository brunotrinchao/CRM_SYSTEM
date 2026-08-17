<?php

namespace Tests\Feature;

use App\Models\ProductPhoto;
use Tests\TestCase;

class ProductFormGetUploadedFileUrlTest extends TestCase
{
    public function test_get_uploaded_file_returns_clean_cloudinary_url_without_storage_prefix(): void
    {
        $rawUrl = 'https://res.cloudinary.com/dpeaqezkb/image/upload/v1786843091/CRM/en1eppcrhztywvg3yuhu.jpg?expires=1786845599&signature=a1e8b69a8189225465419ca87de2d5dc1d43df938f78953e9bbea9dd71a700db';

        $cleanUrl = ProductPhoto::cleanImageUrl($rawUrl);

        $this->assertEquals($rawUrl, $cleanUrl);
        $this->assertStringStartsNotWith('http://127.0.0.1:8000/storage', $cleanUrl);
    }
}
