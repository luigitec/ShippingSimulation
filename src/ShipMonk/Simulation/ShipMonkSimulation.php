<?php

namespace ShipMonk\Simulation;

use DateMalformedStringException;
use DateTimeImmutable;
use ShipMonk\Services\FulfillmentQueue;
use SortedList\Exception\EmptyListException;
use SortedList\Exception\InvalidTypeException;

class ShipMonkSimulation
{
    /**
     * @throws EmptyListException
     * @throws DateMalformedStringException
     * @throws InvalidTypeException
     */
    public static function run(): void
    {
        echo "🚚 SIMULATION: Typical day\n";
        echo str_repeat("=", 70) . "\n\n";

        $fulfillment = new FulfillmentQueue();

        echo "🌅 6:00 AM - E-commerce night orders\n";
        $fulfillment->addOrder(
            customer: "Amazon Store - ElectroShop",
            priority: 3, // Standard
            destination: "Los Angeles, CA",
            items: [
                ['name' => 'iPhone Cases', 'quantity' => 5],
                ['name' => 'Screen Protectors', 'quantity' => 10]
            ],
            weight: 0.8,
            shippingMethod: "ShipMonk Ground"
        );

        sleep(1);

        echo "🌅 6:30 AM - Premium orders from VIP clients\n";
        $fulfillment->addOrder(
            customer: "Shopify Store - FashionBrand",
            priority: 2, // Premium
            destination: "New York, NY",
            items: [
                ['name' => 'Designer T-Shirts', 'quantity' => 3],
                ['name' => 'Branded Hoodies', 'quantity' => 2]
            ],
            weight: 1.2,
            shippingMethod: "FedEx 2-Day",
            promisedDelivery: new DateTimeImmutable('+2 days 17:00')
        );

        echo "🌅 7:00 AM - B2B Express\n";
        $fulfillment->addOrder(
            customer: "Corporate Client - Dr. Squatch",
            priority: 1, // Express
            destination: "Marina del Rey, CA",
            items: [
                ['name' => 'Rugged & Ready 4-Pack', 'quantity' => 100],
                ['name' => 'Deodorant 6-Pack', 'quantity' => 20]
            ],
            weight: 15.5,
            shippingMethod: "ShipMonk Overnight",
            promisedDelivery: new DateTimeImmutable('+1 day 10:00')
        );

        echo "🌅 7:30 AM - Standard Orders\n";
        $fulfillment->addOrder(
            customer: "WooCommerce - HomeDecor",
            priority: 3, // Standard
            destination: "Chicago, IL",
            items: [
                ['name' => 'Decorative Pillows', 'quantity' => 4],
                ['name' => 'Wall Art', 'quantity' => 2]
            ],
            weight: 3.2,
            shippingMethod: "ShipMonk 3-Day Select"
        );

        echo "🌅 8:00 AM - Economy Bulk Orders\n";
        $fulfillment->addOrder(
            customer: "Bulk Reseller - WholesaleHub",
            priority: 4, // Economy
            destination: "Phoenix, AZ",
            items: [
                ['name' => 'Phone Accessories', 'quantity' => 50],
                ['name' => 'Chargers', 'quantity' => 25]
            ],
            weight: 8.7,
            shippingMethod: "ShipMonk Ground Advantage"
        );

        echo "🌅 8:15 AM - Another Critical Express Order\n";
        $fulfillment->addOrder(
            customer: "Medical Supply Co",
            priority: 1, // Express
            destination: "Miami, FL",
            items: [
                ['name' => 'Medical Devices', 'quantity' => 3],
                ['name' => 'Safety Equipment', 'quantity' => 10]
            ],
            weight: 5.1,
            shippingMethod: "ShipMonk Priority Overnight",
            promisedDelivery: new DateTimeImmutable('+1 day 08:00')
        );

        $fulfillment->showFulfillmentStats();

        $atRisk = $fulfillment->getOrdersAtRisk();
        if (!empty($atRisk)) {
            echo "⚠️  SLA RISK ORDERS:\n";
            foreach ($atRisk as $order) {
                echo "   🚨 {$order->orderNumber} - {$order->customer}\n";
            }
            echo "\n";
        }

        echo "LAUNCHING FULFILLMENT - Warehouse Operations\n";
        echo str_repeat("-", 50) . "\n";

        $ordersProcessed = 0;
        while ($fulfillment->getPendingCount() > 0 && $ordersProcessed < 4) {
            $nextOrder = $fulfillment->peekNextOrder();
            if ($nextOrder) {
                echo "Next order in line: {$nextOrder->orderNumber}\n";
                echo "Priority: {$nextOrder->priority} - {$nextOrder->customer}\n";
            }

            $processedOrder = $fulfillment->processNextOrder();
            if ($processedOrder) {
                $processingTime = match($processedOrder->priority) {
                    1 => 15, // minutes...
                    2 => 25,
                    3 => 35,
                    4 => 45
                };
                echo "Estimated time of processing: {$processingTime} minutes\n";
                echo "Order is ready to ship: {$processedOrder}\n\n";
            }

            $ordersProcessed++;
            sleep(1);
        }

        echo "Display the stats after the morning shift::\n";
        $fulfillment->showFulfillmentStats();

        if ($fulfillment->getPendingCount() > 0) {
            echo "We still have " . $fulfillment->getPendingCount() . " pending orders for the 2nd shift\n";
            if ($fulfillment->hasExpressOrders()) {
                echo "🚨 ATTENTION! There are Express pending orders - please prioritize them\n";
            }
            echo "\n";
        } else {
            echo "All orders from the morning shift were processed!!\n";
        }
    }
}