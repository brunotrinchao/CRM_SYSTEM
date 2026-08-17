<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'image',
        'description',
        'order',
        'active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Limpa e formata a URL da imagem.
     * Se contiver o prefixo local /storage/ envelopando uma URL remota (como Cloudinary),
     * remove o prefixo local e retorna a URL real salva.
     */
    public static function cleanImageUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $decoded = urldecode($url);

        // Se houver um 'https://' ou 'http://' após '/storage/', extrai apenas a URL remota de destino
        if (preg_match('/storage\/(https?:\/\/.*)$/i', $decoded, $matches)) {
            return $matches[1];
        }

        // Se já for uma URL remota completa (http:// ou https://)
        if (str_starts_with($decoded, 'http://') || str_starts_with($decoded, 'https://')) {
            return $decoded;
        }

        // Se for um caminho relativo no storage local
        return Storage::disk('public')->url(ltrim($url, '/'));
    }

    /**
     * Accessor para pegar a URL limpa da foto.
     */
    public function getUrlAttribute(): ?string
    {
        return static::cleanImageUrl($this->image);
    }
}
