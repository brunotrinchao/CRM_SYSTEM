<?php

namespace Tests\Feature;

use App\Models\Deal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealSeederMathConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_seeded_deals_have_consistent_product_totals_and_discounts(): void
    {
        $this->seed();

        $deals = Deal::with('products')->get();

        $this->assertNotEmpty($deals);

        foreach ($deals as $deal) {
            $expectedTotal = 0.00;
            $expectedDiscount = 0.00;

            foreach ($deal->products as $product) {
                $qty = $product->pivot->quantity;
                $unitPrice = (float) $product->pivot->unit_price;
                $discount = (float) $product->pivot->discount;
                $subtotal = $unitPrice * $qty;
                $itemTotal = max(0, $subtotal - $discount);

                $expectedTotal += $itemTotal;
                $expectedDiscount += $discount;

                $this->assertEqualsWithDelta(
                    round($itemTotal, 2),
                    round((float) $product->pivot->total_price, 2),
                    0.01,
                    "Total do item de produto no negócio #{$deal->id} não é igual ao cálculo."
                );
            }

            $this->assertEqualsWithDelta(
                round($expectedTotal, 2),
                round((float) $deal->total_value, 2),
                0.01,
                "Valor total do negócio #{$deal->id} não corresponde à soma dos produtos."
            );

            $this->assertEqualsWithDelta(
                round($expectedDiscount, 2),
                round((float) $deal->discount, 2),
                0.01,
                "Desconto total do negócio #{$deal->id} não corresponde à soma dos descontos dos produtos."
            );
        }
    }
}
