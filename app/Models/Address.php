<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'street',
        'number',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'country',
        'reference',
        'type',
    ];

    /**
     * Inclui o accessor full_address no toArray()/toJson(),
     * necessário para o infolist (RepeatableEntry) exibir o endereço completo.
     */
    protected $appends = ['full_address'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->street,
            $this->number ? "nº {$this->number}" : null,
            $this->complement,
            $this->neighborhood,
            $this->city,
            $this->state,
            $this->zip_code,
        ]));
    }
}
