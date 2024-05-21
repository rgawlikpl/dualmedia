<?php

namespace App\Service\Pricing;

use Doctrine\Common\Collections\Collection;

class DiscountPriceCalculator implements PriceCalculatorInterface
{
    private float $discountRate;

    public function __construct(float $discountRate)
    {
        $this->discountRate = $discountRate;
    }

    public function calculatePrice(float $basePrice): float
    {
        return $basePrice * (1 - $this->discountRate);
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
