<?php

namespace App\Models;

use App\Enums\DiscountRequestStatus;
use BokshornIt\FilamentActivityTimeline\Contracts\ProvidesActivityTitle;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class Deal extends Model implements ProvidesActivityTitle
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $casts = [
        'status' => \App\Enums\DealStatus::class,
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'last_contact_date' => 'date',
    ];

    protected $fillable = [
        'user_id',
        'created_by',
        'client_id',
        'product_id',
        'title',
        'quantity',
        'discount',
        'total_value',
        'status',
        'probability',
        'notes',
        'expected_close_date',
        'actual_close_date',
        'last_contact_date',
        'loss_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'deal_product')
            ->withPivot(['quantity', 'discount', 'unit_price', 'total_price'])
            ->withTimestamps();
    }

    public function notesList()
    {
        return $this->hasMany(DealNote::class);
    }

    public function discountRequests()
    {
        return $this->hasMany(DiscountRequest::class);
    }

    public function hasPendingDiscount(): bool
    {
        return $this->discountRequests()->where('status', DiscountRequestStatus::PENDING)->exists();
    }

    public function getIcon(){
        return Phosphor::HandshakeFill;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('deal')
            ->logOnly([
                'status',
                'title',
                'notes',
                'product_id',
                'quantity',
                'client_id',
                'discount',
                'total_value',
                'probability',
                'expected_close_date',
                'actual_close_date',
                'loss_reason',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'criou este negócio',
                'updated' => 'atualizou este negócio',
                'deleted' => 'excluiu este negócio',
                'restored' => 'restaurou este negócio',
                default => $eventName,
            });
    }

    public function activityTitle(): ?string
    {
        return $this->title;
    }
}
