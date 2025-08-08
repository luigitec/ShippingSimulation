<?php

declare(strict_types=1);

namespace ShipMonk\Models;

use DateTimeImmutable;
use ShipMonk\Enums\ShippingPriority;

readonly class ShippingOrder
{
    public function __construct(
        public string             $orderNumber,
        public int                $priority,        // 1=Express, 2=Premium, 3=Standard, 4=Economy
        public string             $customer,
        public string             $destination,
        public array              $items,
        public float              $weight,        // I guess pounds
        public string             $shippingMethod,
        public DateTimeImmutable  $orderTime,
        public ?DateTimeImmutable $promisedDelivery = null,
    ) {}

    public function getTotalItems(): int
    {
        return array_sum(array_column($this->items, 'quantity'));
    }

    public function getItemsDescription(): string
    {
        $descriptions = [];
        foreach ($this->items as $item) {
            $descriptions[] = "{$item['quantity']}x {$item['name']}";
        }
        return implode(', ', $descriptions);
    }

    public function __toString(): string
    {
        $urgency = match($this->priority) {
            ShippingPriority::EXPRESS->value => ShippingPriority::EXPRESS->getLabel(),
            ShippingPriority::PREMIUM->value => ShippingPriority::PREMIUM->getLabel(),
            ShippingPriority::STANDARD->value => ShippingPriority::STANDARD->getLabel(),
            ShippingPriority::ECONOMY->value => ShippingPriority::ECONOMY->getLabel(),
            default => ShippingPriority::ECONOMY->value,
        };

        $promised = $this->promisedDelivery
            ? " (Delivery: {$this->promisedDelivery->format('d/m H:i')})"
            : '';

        return sprintf(
            "%s [%s] - %s → %s | %s | %.1flb%s",
            $this->orderNumber,
            $urgency,
            $this->customer,
            $this->destination,
            $this->getItemsDescription(),
            $this->weight,
            $promised
        );
    }
}
