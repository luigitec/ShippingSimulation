<?php

namespace SortedList\Exception;

use Throwable;

class EmptyListException extends SortedListException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message ?: 'List is empty', $code, $previous);
    }
}