<?php

declare(strict_types=1);

namespace ShipMonk\Enums;
enum ShippingPriority: int
{
    case EXPRESS = 1;
    case PREMIUM = 2;
    case STANDARD = 3;
    case ECONOMY = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::EXPRESS => 'Express',
            self::PREMIUM => 'Premium',
            self::STANDARD => 'Standard',
            self::ECONOMY => 'Economy',
        };
    }
}
