<?php

namespace App\Console\Commands;

use App\Enums\DiscountRequestType;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class BackfillDiscountRequestActivityLog extends Command
{
    protected $signature = 'discount-requests:backfill-activity-log';
    protected $description = 'Backfill activity_log for discount_request: resolve user IDs → names, format amount';

    protected array $userCache = [];

    protected array $logged = [
        'deal_id', 'requested_by', 'type', 'amount', 'reason',
        'status', 'reviewed_by', 'reviewed_at', 'review_note',
    ];

    public function handle(): int
    {
        $activities = Activity::where('log_name', 'discount_request')->get();

        $this->info("Processing {$activities->count()} activities...");

        $count = 0;

        foreach ($activities as $activity) {
            $properties = $activity->properties->toArray();

            // Hydrate empty 'created' activities from subject model
            if ($activity->event === 'created' && empty($properties['attributes'] ?? [])) {
                /** @var \App\Models\DiscountRequest|null $subject */
                $subject = $activity->subject;

                if ($subject) {
                    foreach ($this->logged as $field) {
                        $raw = $subject->getRawOriginal($field);
                        if ($raw !== null) {
                            $properties['attributes'][$field] = $raw;
                        }
                    }
                }
            }

            $changed = false;

            foreach (['attributes', 'old'] as $section) {
                if (empty($properties[$section])) {
                    continue;
                }

                // Resolve user IDs → names (only if still numeric)
                foreach (['requested_by', 'reviewed_by'] as $field) {
                    $val = $properties[$section][$field] ?? null;

                    if ($val !== null && is_numeric($val)) {
                        $properties[$section][$field] = $this->resolveUser($val);
                        $changed = true;
                    }
                }

                // Format amount (only if still numeric)
                $amount = $properties[$section]['amount'] ?? null;

                if ($amount !== null && is_numeric($amount)) {
                    $typeValue = $properties['attributes']['type'] ?? null;
                    $properties[$section]['amount'] = $typeValue === DiscountRequestType::PERCENT->value
                        ? number_format((float) $amount, 2, ',', '.') . '%'
                        : 'R$ ' . number_format((float) $amount, 2, ',', '.');
                    $changed = true;
                }
            }

            if ($changed) {
                $activity->properties = collect($properties);
                $activity->saveQuietly();
                $count++;
            }
        }

        $this->info("Done. Updated {$count} activities.");

        return self::SUCCESS;
    }

    protected function resolveUser(mixed $id): string
    {
        $key = (string) $id;

        if (! isset($this->userCache[$key])) {
            $this->userCache[$key] = User::find($id)?->name ?? $key;
        }

        return $this->userCache[$key];
    }
}
