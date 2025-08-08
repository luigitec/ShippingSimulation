<?php

declare(strict_types=1);

namespace SortedList;

class Node
{
    public function __construct(public string|int $data, public ?Node $next = null)
    {
        // We just need to initialize the data and the next pointer
    }
}
