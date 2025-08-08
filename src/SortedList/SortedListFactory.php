<?php

namespace SortedList;

class SortedListFactory
{
    public static function forStrings(): SortedLinkedList
    {
        return new SortedLinkedList('string');
    }

    public static function forIntegers(): SortedLinkedList
    {
        return new SortedLinkedList('int');
    }

    /**
     * @throws Exception\InvalidTypeException
     */
    public static function fromArray(array $values): SortedLinkedList
    {
        $list = new SortedLinkedList();
        foreach ($values as $value) {
            $list->add($value);
        }
        return $list;
    }
}
