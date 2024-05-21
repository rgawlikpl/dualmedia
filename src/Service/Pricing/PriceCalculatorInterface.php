<?php

namespace App\Service\Pricing;

use Doctrine\Common\Collections\Collection;

interface PriceCalculatorInterface
{
    public function calculateTotalPrice(Collection $products): float;
}