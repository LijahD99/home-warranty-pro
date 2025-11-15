<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Get the properties for the user.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get the tickets submitted by the user.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the tickets assigned to the user.
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Get the comments made by the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // ============================================
    // Domain Logic - Role Checks
    // ============================================

    /**
     * Check if user is homeowner.
     */
    public function isHomeowner(): bool
    {
        return $this->role === UserRole::HOMEOWNER;
    }

    /**
     * Check if user is builder/manager.
     */
    public function isBuilder(): bool
    {
        return $this->role === UserRole::BUILDER;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles.
     *
     * @param UserRole[] $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    // ============================================
    // Domain Logic - Business Queries
    // ============================================

    /**
     * Get count of properties owned by user.
     */
    public function getPropertiesCount(): int
    {
        return $this->properties()->count();
    }

    /**
     * Get count of tickets submitted by user.
     */
    public function getSubmittedTicketsCount(): int
    {
        return $this->tickets()->count();
    }

    /**
     * Get count of tickets assigned to user.
     */
    public function getAssignedTicketsCount(): int
    {
        return $this->assignedTickets()->count();
    }

    /**
     * Get count of open tickets assigned to user.
     */
    public function getOpenAssignedTicketsCount(): int
    {
        return $this->assignedTickets()
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count();
    }

    /**
     * Get count of comments made by user.
     */
    public function getCommentsCount(): int
    {
        return $this->comments()->count();
    }

    /**
     * Check if user has any properties.
     */
    public function hasProperties(): bool
    {
        return $this->properties()->exists();
    }

    /**
     * Check if user has any open tickets.
     */
    public function hasOpenTickets(): bool
    {
        return $this->tickets()
            ->whereIn('status', ['submitted', 'assigned', 'in_progress', 'complete'])
            ->exists();
    }

    /**
     * Check if user has any assigned tickets.
     */
    public function hasAssignedTickets(): bool
    {
        return $this->assignedTickets()->exists();
    }

    // ============================================
    // Domain Logic - Display & Formatting
    // ============================================

    /**
     * Get user's role display name.
     */
    public function getRoleDisplayName(): string
    {
        return $this->role->getDisplayName();
    }
}
