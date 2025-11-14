<?php

namespace App\Exceptions;

use Exception;

class InvalidStatusTransitionException extends Exception
{
    public static function fromTo(string $from, string $to): self
    {
        return new self("Invalid status transition from '{$from}' to '{$to}'");
    }
}
