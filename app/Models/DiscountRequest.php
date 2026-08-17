<?php

namespace App\Models;

use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DiscountRequest extends Model implements ProvidesActivityTitle
{
    use HasFactory, LogsActivity;

    protected $casts = [
        'type' => \App\Enums\DiscountRequestType::class,
        'status' => \App\Enums\DiscountRequestStatus::class,
        'reviewed_at' => 'datetime',
    ];

    protected $fillable = [
        'deal_id',
        'requested_by',
        'type',
        'amount',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'original_discount',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getIcon(){
        return Phosphor::SealPercentFill;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('discount_request')
            ->logOnly([
                'deal_id',
                'requested_by',
                'type',
                'amount',
                'reason',
                'status',
                'reviewed_by',
                'reviewed_at',
                'review_note',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'criou esta solicitação de desconto',
                'updated' => 'atualizou esta solicitação de desconto',
                'deleted' => 'excluiu esta solicitação de desconto',
                default   => $eventName,
            })
            ;
    }

    public function tapActivity(\Spatie\Activitylog\Models\Activity $activity, string $eventName): void
    {
        $properties = $activity->properties->toArray();

        // logOnlyDirty() → getDirty() empty after save → attributes: [] on 'created'.
        // Manually populate from model attributes so the timeline has something to show.
        if ($eventName === 'created' && empty($properties['attributes'] ?? [])) {
            $logged = ['deal_id', 'requested_by', 'type', 'amount', 'reason', 'status', 'reviewed_by', 'reviewed_at', 'review_note'];

            foreach ($logged as $field) {
                $raw = $this->getRawOriginal($field);

                if ($raw === null) {
                    continue;
                }

                $properties['attributes'][$field] = $raw;
            }
        }

        foreach (['attributes', 'old'] as $section) {
            if (empty($properties[$section])) {
                continue;
            }

            // Resolver IDs de usuário → nome
            foreach (['requested_by', 'reviewed_by'] as $field) {
                $val = $properties[$section][$field] ?? null;

                if ($val !== null && is_numeric($val)) {
                    $user = User::find($val);
                    $properties[$section][$field] = $user?->name ?? $val;
                }
            }

            // Formatar amount conforme type da solicitação
            $amount = $properties[$section]['amount'] ?? null;

            if ($amount !== null && is_numeric($amount)) {
                $typeValue = $properties['attributes']['type']
                    ?? $this->type?->value
                    ?? null;

                $properties[$section]['amount'] = $typeValue === \App\Enums\DiscountRequestType::PERCENT->value
                    ? number_format((float) $amount, 2, ',', '.') . '%'
                    : 'R$ ' . number_format((float) $amount, 2, ',', '.');
            }
        }

        $activity->properties = collect($properties);
    }

    public function activityTitle(): ?string
    {
        return $this->reason;
    }
}
