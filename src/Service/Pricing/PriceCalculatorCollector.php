<?php

namespace App\Service\Pricing;

use App\Entity\Order;

class PriceCalculatorCollector
{
    private iterable $calculators;

    public function __construct(iterable $calculators)
    {
        $this->calculators = $calculators;
    }

    public function calculateTotals(Order &$order): array
    {
        $products = $order->getOrderProducts();

        $baseTotal = 0;
        $discountTotal = 0;
        $vatTotal = 0;
        $itemCount = $products->count();

        foreach ($products as $product) {
            $baseTotal += ($product->getProduct()->getPrice() * $product->getQuantity());
        }

        foreach ($this->calculators as $calculator) {
            if ($calculator instanceof DiscountPriceCalculator) {
                $discountTotal = $calculator->calculateTotalPrice($products);
            } elseif ($calculator instanceof VatPriceCalculator) {
                $vatTotal = $calculator->calculateTotalPrice($products);
            }
        }

        return [
            'baseTotal' => round($baseTotal,2),
            'discountTotal' => round($discountTotal,2),
            'vatTotal' => round($vatTotal,2),
            'itemCount' => $itemCount,
        ];
    }
}
