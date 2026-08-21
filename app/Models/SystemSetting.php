<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'company_logo',
    ];

    public static function getSetting(): self
    {
        return self::firstOrCreate([], [
            'company_name' => config('app.name', 'CRM System'),
            'company_logo' => null,
        ]);
    }

    public static function getCompanyName(): string
    {
        return self::getSetting()->company_name ?: config('app.name', 'CRM System');
    }

    public static function getCompanyLogoUrl(): ?string
    {
        $logo = self::getSetting()->company_logo;

        if ($logo && Storage::disk('public')->exists($logo)) {
            return Storage::disk('public')->url($logo);
        }

        return null;
    }
}
