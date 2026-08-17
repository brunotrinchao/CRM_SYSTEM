<?php

namespace App\Models;

use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class Client extends Model implements ProvidesActivityTitle
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'origin' => \App\Enums\ClientOrigin::class,
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'cellphone',
        'origin',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function getIcon(){
        return Phosphor::UserFill;
    }

    public function activityTitle(): ?string
    {
        return $this->name;
    }
}
