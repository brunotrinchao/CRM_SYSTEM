<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductService
{
    public static function create(array $data): Product
    {
        $data['user_id'] = Auth::id();

        $photos = $data['photos'] ?? [];
        unset($data['photos']);

        $product = Product::create($data);

        self::syncPhotos($product, $photos);

        return $product;
    }

    public static function update(Product $product, array $data): Product
    {
        $photos = $data['photos'] ?? null;
        unset($data['photos']);

        $product->update($data);

        if (is_array($photos)) {
            self::syncPhotos($product, $photos);
        }

        return $product;
    }

    /**
     * Sincroniza fotos do produto.
     *
     * O campo FlexImageUpload (multiple) entrega um array de paths (strings).
     * Fotos removidas do upload são apagadas; novas são criadas.
     */
    private static function syncPhotos(Product $product, array $photos): void
    {
        // normaliza: aceita strings (FileUpload) ou arrays (repeater legacy)
        $normalized = collect($photos)
            ->map(fn ($photo) => is_array($photo) ? ($photo['image'] ?? null) : $photo)
            ->filter()
            ->values()
            ->all();

        $product->photos()->whereNotIn('image', $normalized)->delete();

        $existing = $product->photos()->pluck('image')->all();

        foreach ($normalized as $path) {
            if (in_array($path, $existing, true)) {
                continue;
            }

            $product->photos()->create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'image' => $path,
            ]);
        }
    }
}
