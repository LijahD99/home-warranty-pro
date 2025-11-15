<?php

namespace App\Exceptions;

use App\Enums\UserRole;
use Exception;

class InvalidCommentException extends Exception
{
    public static function emptyComment(): self
    {
        return new self('Comment text cannot be empty');
    }

    public static function notAuthorized(string $reason): self
    {
        return new self("Not authorized to perform this action: {$reason}");
    }

    public static function cannotMarkAsInternal(UserRole $userRole): self
    {
        return new self("User with role '{$userRole->value}' cannot create internal comments. Only builders and admins can create internal comments.");
    }
}
