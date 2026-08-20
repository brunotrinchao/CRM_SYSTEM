<?php

namespace App\Services;

use App\Enums\DealStatus;
use App\Enums\DiscountRequestStatus;
use App\Models\Deal;
use App\Models\DiscountRequest;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;

class DealService
{
    public static function create(array $data): Deal
    {
        $noteData = self::extractNoteData($data);

        if (empty($data['created_by']) && Auth::check()) {
            $data['created_by'] = Auth::id();
        }

        if (empty($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $products = $data['products'] ?? [];
        unset($data['products']);

        $deal = Deal::create($data);

        if(!empty($products)){
            self::syncProducts($deal, $products);
        }

        if(!empty($noteData)){
            self::handleNote($deal, $noteData);
        }

        return $deal;
    }

    public static function update(Deal $deal, array $data): Deal
    {
        $noteData = self::extractNoteData($data);

        if (isset($data['status'])) {
            $newStatus = is_string($data['status']) ? DealStatus::tryFrom($data['status']) : $data['status'];

            if ($newStatus === DealStatus::CANCELLED && $deal->status !== DealStatus::CANCELLED) {
                if (empty($data['confirm_status_cancelled'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'confirm_status_cancelled' => 'É necessário confirmar a chave de confirmação para cancelar o negócio (ação irreversível).',
                    ]);
                }
            }

            if ($newStatus === DealStatus::LOST && $deal->status !== DealStatus::LOST) {
                if (empty($data['confirm_status_lost'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'confirm_status_lost' => 'É necessário confirmar a chave de confirmação para marcar o negócio como Perdido.',
                    ]);
                }
            }
        }

        unset($data['confirm_status_cancelled'], $data['confirm_status_lost']);

        // Repeater sem ->relationship() é dehydrated(true): produtos chegam em $data.
        $products = $data['products'] ?? null;
        unset($data['products']);

        $deal->update($data);

        if ($products !== null) {
            self::syncProducts($deal, array_values($products));
        }

        self::handleNote($deal, $noteData);

        return $deal;
    }

    private static function extractNoteData(array &$data): array
    {
        $fields = ['interaction_type', 'contact_date', 'content', 'next_follow_up_date', 'next_action'];
        $noteData = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $noteData[$field] = $data[$field];
                unset($data[$field]);
            }
        }
        return $noteData;
    }

    private static function handleNote(Deal $deal, array $noteData): void
    {
        $hasContent = !empty($noteData['content'])
            || !empty($noteData['interaction_type'])
            || !empty($noteData['contact_date'])
            || !empty($noteData['next_follow_up_date'])
            || !empty($noteData['next_action']);

        if ($hasContent) {
            $payload = array_merge([
                'deal_id' => $deal->id,
                'user_id' => Auth::id() ?? $deal->user_id,
                'interaction_type' => \App\Enums\ChannelNote::OTHER->value,
                'content' => 'Contato registrado.',
                'contact_date' => now(),
            ], array_filter($noteData, fn ($val) => !is_null($val) && $val !== ''));

            DealNoteService::create($payload);
        }
    }

    public static function transfer(Deal $deal, User $user): Deal
    {
        $deal->update([
            'user_id' => $user->id
        ]);

        return $deal;
    }

    private static function syncProducts(Deal $deal, array $products): void
    {
        $pivot = [];

        foreach (array_values($products) as $item) {
            $productId = $item['product_id'] ?? null;

            if (!$productId) {
                continue;
            }

            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);

            $pivot[$productId] = [
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $price * $qty,
                'discount' => 0,
            ];
        }

        $deal->products()->sync($pivot);
    }

    public static function requestDicount(array $data): DiscountRequest
    {
        if (isset($data['deal_id'])) {
            $deal = Deal::find($data['deal_id']);
            if ($deal) {
                if (! in_array($deal->status, [DealStatus::PENDING, DealStatus::NEGOTIATING])) {
                    throw new \InvalidArgumentException('Solicitação de desconto só é permitida para negócios em Pendente ou Negociação.');
                }

                if ($deal->status === DealStatus::PENDING) {
                    $deal->update([
                        'status' => DealStatus::NEGOTIATING,
                    ]);
                }
            }
        }

        $data['requested_by'] = Auth::id();

        unset($data['total_value']);

        $data['type'] = !$data['type'] ? 'VALUE' : 'PERCENT';

        $discount = DiscountRequest::create($data);

        return $discount;
    }

    public static function approveDiscount(int $discountRequestId): void
{
    $discountRequest = DiscountRequest::findOrFail($discountRequestId);

    $discountRequest->update([
        'status' => DiscountRequestStatus::APPROVED,
        'reviewed_at' => now(),
    ]);

    if ($discountRequest->deal) {
        $discountRequest->deal->update([
            'discount' => $discountRequest->amount,
        ]);
    }

    // Notificar o solicitante sobre a aprovação
    if ($discountRequest->requester) {
        Notification::make()
            ->title('Solicitação de Desconto Aprovada')
            ->body(auth()->user()->name . ' aprovou a solicitação de desconto.')
            ->success()
            ->sendToDatabase($discountRequest->requester)
            
        // Se também quiser enviar em tempo real via broadcast/websocket do Filament:
        ->send($discountRequest->requester);
    }
}

public static function rejectDiscount(int $discountRequestId): void
{
    $discountRequest = DiscountRequest::findOrFail($discountRequestId);

    $discountRequest->update([
        'status' => DiscountRequestStatus::REJECTED,
        'reviewed_at' => now(),
    ]);

    // Notificar o solicitante sobre a rejeição
    if ($discountRequest->requester) {
        Notification::make()
            ->title('Solicitação de Desconto Recusada')
            ->body(auth()->user()->name . ' recusou a solicitação de desconto.')
            ->danger()
            ->sendToDatabase($discountRequest->requester)
            ->send($discountRequest->requester);
    }
}
}