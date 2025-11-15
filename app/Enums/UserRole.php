<?php

namespace App\Enums;

enum UserRole: string
{
    case HOMEOWNER = 'homeowner';
    case BUILDER = 'builder';
    case ADMIN = 'admin';

    /**
     * Get display name for the role.
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::HOMEOWNER => 'Homeowner',
            self::BUILDER => 'Builder/Manager',
            self::ADMIN => 'Administrator',
        };
    }

    /**
     * Check if role can create properties.
     */
    public function canCreateProperties(): bool
    {
        return match ($this) {
            self::HOMEOWNER, self::ADMIN => true,
            self::BUILDER => false,
        };
    }

    /**
     * Check if role can create tickets.
     */
    public function canCreateTickets(): bool
    {
        return match ($this) {
            self::HOMEOWNER => true,
            self::BUILDER, self::ADMIN => false,
        };
    }

    /**
     * Check if role can be assigned tickets.
     */
    public function canBeAssignedTickets(): bool
    {
        return match ($this) {
            self::BUILDER, self::ADMIN => true,
            self::HOMEOWNER => false,
        };
    }

    /**
     * Check if role can create internal comments.
     */
    public function canCreateInternalComments(): bool
    {
        return match ($this) {
            self::BUILDER, self::ADMIN => true,
            self::HOMEOWNER => false,
        };
    }

    /**
     * Check if role can view all tickets.
     */
    public function canViewAllTickets(): bool
    {
        return match ($this) {
            self::BUILDER, self::ADMIN => true,
            self::HOMEOWNER => false,
        };
    }

    /**
     * Check if role can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this === self::ADMIN;
    }
}
