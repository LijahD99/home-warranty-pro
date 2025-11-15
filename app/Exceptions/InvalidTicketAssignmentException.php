<?php

namespace App\Exceptions;

use App\Enums\UserRole;
use Exception;

class InvalidTicketAssignmentException extends Exception
{
    public static function userNotAuthorized(UserRole $role): self
    {
        return new self("User with role '{$role->value}' cannot be assigned tickets. Only builders and admins can be assigned.");
    }

    public static function ticketNotInValidState(string $currentStatus): self
    {
        return new self("Ticket with status '{$currentStatus}' cannot be assigned. Ticket must be in 'submitted' status.");
    }
}
