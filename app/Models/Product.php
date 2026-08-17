<?php

namespace App\Models;

use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class Product extends Model implements ProvidesActivityTitle
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'active' => 'boolean',
        'price' => 'float',
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'sku',
        'price',
        'observation',
        'current_stock',
        'minimum_stock',
        'active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function photos()
    {
        return $this->hasMany(ProductPhoto::class);
    }

    public function getIcon(){
        return Phosphor::CubeFill;
    }

    public function activityTitle(): ?string
    {
        return $this->name;
    }
}
