<?php

namespace App\Models;

use App\Enums\ChannelNote;
use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DealNote extends Model implements ProvidesActivityTitle
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $casts = [
        'interaction_type' => ChannelNote::class,
        'contact_date' => 'datetime',
        'next_follow_up_date' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'deal_id',
        'interaction_type',
        'content',
        'contact_date',
        'next_follow_up_date',
        'next_action',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('deal')
            ->logOnly([
                'interaction_type',
                'content',
                'notes',
                'contact_date',
                'next_follow_up_date',
                'next_action'
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'criou esta nota',
                'updated' => 'atualizou esta nota',
                'deleted' => 'excluiu esta nota',
                'restored' => 'restaurou esta nota',
                default => $eventName,
            });
    }

    public function activityTitle(): ?string
    {
        return $this->content;
    }

    public function getIcon(): Phosphor
    {
        return Phosphor::NotePencilThin;
    }
}
