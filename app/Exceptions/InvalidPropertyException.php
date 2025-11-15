<?php

namespace App\Exceptions;

use Exception;

class InvalidPropertyException extends Exception
{
    public static function notOwnedByUser(): self
    {
        return new self('Cannot perform this action on a property you do not own');
    }

    public static function hasOpenTickets(int $count): self
    {
        return new self("Cannot delete property with {$count} open ticket(s). Close all tickets first.");
    }

    public static function invalidZipCode(string $zipCode): self
    {
        return new self("Invalid ZIP code format: {$zipCode}");
    }

    public static function invalidState(string $state): self
    {
        return new self("Invalid state code: {$state}. Must be a 2-letter US state code.");
    }
}
