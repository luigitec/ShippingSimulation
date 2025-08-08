<?php

declare(strict_types=1);

namespace SortedList;

use Countable;
use Generator;
use IteratorAggregate;
use JsonSerializable;
use SortedList\Exception\InvalidTypeException;
use SortedList\Exception\EmptyListException;

class SortedLinkedList implements IteratorAggregate, Countable, JsonSerializable
{
    private ?object $head = null;
    private ?string $setType = null;
    private int $size = 0;
    private array $allowedTypes = ['int', 'string'];

    /**
     * @desciption Only int ot strings.
     *
     * @param string|null $type
     * @throws InvalidTypeException
     */
    public function __construct(?string $type = null)
    {

        if ($type !== null && !in_array($type, $this->allowedTypes, true)) {
            // I also like to place logs, but it depends on the project
            throw new InvalidTypeException("The only allowed types are: " . implode(', ', $this->allowedTypes) . ". Given: $type");
        }

        $this->setType = $type;
    }

    /**
     * Adds a value to the sorted linked list in the correct position.
     * If the value type is compatible with the list's type:
     * - If the list is empty or the value is less than or equal to the head, it adds it at the beginning of the list.
     * - Otherwise, it will check the list to find the correct position to insert the new value.
     *
     * @throws InvalidTypeException
     */
    public function add(string|int $value): self
    {
        $this->ensureTypeCompatibility($value);

        $newNode = new Node($value);

        if ($this->head === null || $this->compare($value, $this->head->data) <= 0) {
            $newNode->next = $this->head;
            $this->head = $newNode;
            $this->size++;
            return $this;
        }

        $current = $this->head;
        while ($current->next !== null && $this->compare($value, $current->next->data) > 0) {
            $current = $current->next;
        }

        $newNode->next = $current->next;
        $current->next = $newNode;
        $this->size++;

        return $this;
    }

    /**
     * Removes a value from the sorted linked list. Returns true if removed, false if the value was not found:
     * - If the value is found at the head, it removes it and updates the head pointer to the next node and returns true.
     * - Otherwise, it will check the list to find the value. If found, it removes it and returns true.
     * - Else, it returns false because the value was not found.
     */
    public function remove(string|int $value): bool
    {
        if ($this->head === null) {
            return false;
        }

        if ($this->head->data === $value) {
            $this->head = $this->head->next;
            $this->size--;
            return true;
        }

        $current = $this->head;
        while ($current->next !== null) {
            if ($current->next->data === $value) {
                $current->next = $current->next->next;
                $this->size--;
                return true;
            }
            $current = $current->next;
        }

        return false;
    }

    /**
     * Checks if the sorted linked list contains a specific value.
     * - It iterates through the list, comparing each node's value with the given value.
     * - If it finds a match, it returns true.
     * - If it reaches a point where the current node's value is greater than the given value,
     * it breaks the loop and returns false, as the list is sorted and the value cannot be present after that point.
     */
    public function contains(string|int $value): bool
    {
        $current = $this->head;
        while ($current !== null) {
            $comparison = $this->compare($value, $current->data);
            if ($comparison === 0) {
                return true;
            }
            if ($comparison < 0) {
                break;
            }
            $current = $current->next;
        }

        return false;
    }

    /**
     * Returns the first element of the sorted linked list.
     * @throws EmptyListException
     */
    public function first(): string|int
    {
        if ($this->head === null) {
            throw new EmptyListException('List is empty, cannot get first element');
        }

        return $this->head->data;
    }

    /**
     * Returns the last element of the sorted linked list.
     * @throws EmptyListException
     */
    public function last(): string|int
    {
        if ($this->head === null) {
            throw new EmptyListException('List is empty, cannot get last element');
        }

        $current = $this->head;
        while ($current->next !== null) {
            $current = $current->next;
        }

        return $current->data;
    }

    /**
     * Removes the first element of the sorted linked list.
     * - It checks if the list is empty and throws an exception if it is.
     * - If not empty, it retrieves the value of the first node, updates the head to
     * the next node, decreases the size of the list, and returns the value of the removed node
     *
     * @throws EmptyListException
     */
    public function removeFirst(): string|int
    {
        $value = $this->first();
        $this->head = $this->head->next;
        $this->size--;

        return $value;
    }

    /**
     * Helper public method to get the number of elements in the list.
     */
    public function count(): int
    {
        return $this->size;
    }

    /**
     * Helper public method to clear the sorted linked list.
     */
    public function clear(): self
    {
        $this->head = null;
        $this->size = 0;
        return $this;
    }

    /**
     * Helper public method to check if the list is empty by checking if the head is null,
     * which indicates that the list is empty.
     */
    public function isEmpty(): bool
    {
        return $this->head === null;
    }

    /**
     * Helper public method to get the array representation of the sorted linked list.
     */
    public function toArray(): array
    {
        $result = [];
        $current = $this->head;
        while ($current !== null) {
            $result[] = $current->data;
            $current = $current->next;
        }
        return $result;
    }

    /**
     * Helper public method to get an iterator for the sorted linked list.
     * This allows the list to be iterated over using a foreach loop.
     */
    public function getIterator(): Generator
    {
        $current = $this->head;
        while ($current !== null) {
            yield $current->data;
            $current = $current->next;
        }
    }

    /**
     * Helper public method to get the value at a specific index in the sorted linked list.
     * - It checks if the index is within bounds (0 to size - 1).
     * - If the index is valid, it iterates through the list until it reaches the specified index
     * and returns the value at that index.
     * - If the index is out of bounds, it throws an OutOfBoundsException.
     */
    public function get(int $index): string|int
    {
        if ($index < 0 || $index >= $this->size) {
            throw new \OutOfBoundsException("Index {$index} out of bounds for size {$this->size}");
        }

        $current = $this->head;
        for ($i = 0; $i < $index; $i++) {
            $current = $current->next;
        }
        return $current->data;
    }

    /**
     * Helper public method to get the JSON representation of the sorted linked list.
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->setType,
            'size' => $this->size,
            'data' => $this->toArray()
        ];
    }

    /**
     * Helper public method to find the index of a specific value in the sorted linked list.
     * - It iterates through the list, comparing each node's value with the given value.
     * - If it finds a match, it returns the index of that value.
     * - If it reaches a point where the current node's value is greater than the given value,
     * it breaks the loop and returns -1, indicating that the value was not found.
     */
    public function indexOf(string|int $value): int
    {
        $current = $this->head;
        $index = 0;

        while ($current !== null) {
            if ($current->data === $value) {
                return $index;
            }
            if ($current->data > $value) {
                break;
            }
            $current = $current->next;
            $index++;
        }

        return -1;
    }

    /**
     * Helper public method to convert the sorted linked list to a string representation.
     * - If the list is empty, it returns '[]'.
     * - Otherwise, it iterates through the list, converts each value to a string,
     * and joins them with commas, returning a string in the format '[value1, value2, ...]'.
     */
    public function __toString(): string
    {
        if ($this->isEmpty()) {
            return '[]';
        }

        return '[' . implode(', ', array_map('strval', $this->toArray())) . ']';
    }

    /**
     * I ended up using the spaceship operator (<=>) to compare values too much, so I created a private method for it.
     */
    private function compare(string|int $a, string|int $b): int
    {
        return $a <=> $b;
    }

    /**
     * Helps to check if the value is compatible with the allowed types,
     * and it initializes the type if it is the first value being added.
     *
     * @throws InvalidTypeException
     */
    private function ensureTypeCompatibility(mixed $value): void
    {
        $valueType = gettype($value);

        // Normalize types => this gave me a headache
        if ($valueType === 'integer') {
            $valueType = 'int';
        }

        if (!in_array($valueType, $this->allowedTypes, true)) {
            throw new InvalidTypeException(
                sprintf('Only string and int values are supported, %s given', $valueType)
            );
        }

        if ($this->setType === null) {
            $this->setType = $valueType;
            return;
        }

        if ($this->setType !== $valueType) {
            throw new InvalidTypeException(
                sprintf(
                    'Mixed types not allowed. List contains %s values, %s given',
                    $this->setType,
                    $valueType
                )
            );
        }
    }
}


