<?php

namespace App\Service\Pricing;

use Doctrine\Common\Collections\Collection;

class VatPriceCalculator implements PriceCalculatorInterface
{
    private float $vatRate;

    public function __construct(float $vatRate)
    {
        $this->vatRate = $vatRate;
    }

    public function calculatePrice(float $basePrice): float
    {
        return $basePrice * (1 + $this->vatRate);
    }

    public function calculateTotalPrice(Collection $products): float
    {
        $total = 0;
        foreach ($products as $product) {
            $total += $this->calculatePrice($product->getProduct()->getPrice()) * $product->getQuantity();
        }
        return $total;
    }
}
