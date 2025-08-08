<?php

namespace SortedList\Exception;

class InvalidTypeException extends SortedListException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message ?: 'An invalid type was provided', $code, $previous);
    }
}