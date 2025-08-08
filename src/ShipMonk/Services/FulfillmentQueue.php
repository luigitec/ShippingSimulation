<?php

namespace ShipMonk\Services;

use DateMalformedStringException;
use DateTimeImmutable;
use SortedList\Exception\EmptyListException;
use SortedList\Exception\InvalidTypeException;
use SortedList\SortedLinkedList;
use SortedList\SortedListFactory;
use ShipMonk\Models\ShippingOrder;

/**
 * SLA: Service Level Agreement
 * 1. Express: 1 day
 * 2. Premium: 2 days
 * 3. Standard: 3-5 days
 * 4. Economy: 7-10 days
 *
 * This class manages the fulfillment queue for shipping orders. "Strong SLAs" are prioritized to ensure timely delivery.
 */
class FulfillmentQueue
{
    private SortedLinkedList $priorityQueue;
    private array $orders = [];
    private array $orderTracking = [];
    private int $nextOrderId = 1001;

    public function __construct()
    {
        $this->priorityQueue = SortedListFactory::forIntegers();

        echo "ShipMonk Fulfillment System - Launching the Queue Manager\n";
        echo "Priorities: 1=Express (SLA 1 day), 2=Premium (2 days), 3=Standard (3-5 days), 4=Economy (7-10 days)\n\n";
    }

    /**
     * Adds a new shipping order to the fulfillment queue and returns the order number.
     * @throws InvalidTypeException
     */
    public function addOrder(
        string $customer,
        int $priority,
        string $destination,
        array $items,
        float $weight,
        string $shippingMethod,
        ?DateTimeImmutable $promisedDelivery = null
    ): string {
        if ($priority < 1 || $priority > 4) {
            throw new \InvalidArgumentException('Priority must be between 1 (Express) and 4 (Economy).');
        }

        $orderNumber = "SM{$this->nextOrderId}";
        $this->nextOrderId++;

        $order = new ShippingOrder(
            orderNumber: $orderNumber,
            priority: $priority,
            customer: $customer,
            destination: $destination,
            items: $items,
            weight: $weight,
            shippingMethod: $shippingMethod,
            orderTime: new DateTimeImmutable(),
            promisedDelivery: $promisedDelivery
        );

        $this->orders[$orderNumber] = $order;
        $this->orderTracking[$orderNumber] = $orderNumber;

        $this->priorityQueue->add($priority);

        echo "New order has been added: {$order}\n";
        $this->showCurrentQueue();

        return $orderNumber;
    }

    /**
     * Displays the current state of the fulfillment queue.
     * @throws EmptyListException
     */
    public function processNextOrder(): ?ShippingOrder
    {
        if ($this->priorityQueue->isEmpty()) {
            echo "There are no pending orders\n";
            return null;
        }

        $nextPriority = $this->priorityQueue->removeFirst();

        $orderToProcess = null;
        $orderIdToRemove = null;

        foreach ($this->orders as $id => $order) {
            if ($order->priority === $nextPriority) {
                $orderToProcess = $order;
                $orderIdToRemove = $id;
                break;
            }
        }

        if ($orderToProcess) {
            unset($this->orders[$orderIdToRemove]);
            unset($this->orderTracking[$orderToProcess->orderNumber]);

            echo "Order is being processed: {$orderToProcess}\n";
            $this->showCurrentQueue();
            return $orderToProcess;
        }

        return null;
    }

    /**
     * Displays the current state of the fulfillment queue.
     * @throws EmptyListException
     */
    public function peekNextOrder(): ?ShippingOrder
    {
        if ($this->priorityQueue->isEmpty()) {
            return null;
        }

        $nextPriority = $this->priorityQueue->first();

        foreach ($this->orders as $order) {
            if ($order->priority === $nextPriority) {
                return $order;
            }
        }

        return null;
    }

    /**
     * Displays the current state of the fulfillment queue.
     */
    public function findOrder(string $orderNumber): ?ShippingOrder
    {
        if (!isset($this->orderTracking[$orderNumber])) {
            return null;
        }

        $orderId = $this->orderTracking[$orderNumber];
        return $this->orders[$orderId] ?? null;
    }

    /**
     * Reports the fulfillment statistics
     */
    public function showFulfillmentStats(): void
    {
        if ($this->priorityQueue->isEmpty()) {
            echo "The fulfillment queue is empty\n\n";
            return;
        }

        $stats = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $totalWeight = 0;
        $totalItems = 0;

        foreach ($this->orders as $order) {
            $stats[$order->priority]++;
            $totalWeight += $order->weight;
            $totalItems += $order->getTotalItems();
        }

        echo <<<STATS
Fulfillment Stats:
EXPRESS (1): {$stats[1]} orders
PREMIUM (2): {$stats[2]} orders
STANDARD (3): {$stats[3]} orders
ECONOMY (4): {$stats[4]} orders
   ═══════════════════════════
TOTAL ORDERS: {$this->priorityQueue->count()}
TOTAL WEIGHT: {number_format($totalWeight, 1)} pounds
TOTAL ITEMS: {number_format($totalItems)}

STATS;
    }

    /**
     * Display the updated queue by priority
     */
    private function showCurrentQueue(): void
    {
        echo "Fulfillment queue:\n";

        if ($this->priorityQueue->isEmpty()) {
            echo "   (Empty - ready for new orders)\n\n";
            return;
        }

        $prioritiesInOrder = $this->priorityQueue->toArray();
        $shownOrders = [];
        $position = 1;

        foreach ($prioritiesInOrder as $priority) {
            foreach ($this->orders as $order) {
                if ($order->priority === $priority && !in_array($order, $shownOrders, true)) {
                    echo "   {$position}. {$order}\n";
                    $shownOrders[] = $order;
                    $position++;
                    break;
                }
            }
        }
        echo "\n";
    }

    /**
     * Get the count of pending orders in the queue.
     */
    public function getPendingCount(): int
    {
        return $this->priorityQueue->count();
    }

    /**
     * Check if there are any express orders (priority 1) in the queue.
     */
    public function hasExpressOrders(): bool
    {
        return $this->priorityQueue->contains(1);
    }

    /**
     * Returns a list of orders that are at risk of missing their promised delivery date.
     * An order is considered at risk if its promised delivery date is within the next 24 hours (just for example).
     * @throws DateMalformedStringException
     */
    public function getOrdersAtRisk(): array
    {
        $atRisk = [];
        $now = new DateTimeImmutable();

        foreach ($this->orders as $order) {
            if ($order->promisedDelivery && $order->promisedDelivery < $now->modify('+1 day')) {
                $atRisk[] = $order;
            }
        }

        return $atRisk;
    }
}
