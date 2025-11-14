<?php

namespace App\Models;

use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\InvalidTicketAssignmentException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'assigned_to',
        'area_of_issue',
        'description',
        'image_path',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valid status transitions map.
     */
    private const STATUS_TRANSITIONS = [
        'submitted' => ['assigned'],
        'assigned' => ['in_progress'],
        'in_progress' => ['complete'],
        'complete' => ['closed'],
        'closed' => [],
    ];

    /**
     * Get the property associated with the ticket.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the user who submitted the ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user the ticket is assigned to.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the comments for the ticket.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // ============================================
    // Domain Logic - Status Transitions
    // ============================================

    /**
     * Transition ticket to a new status.
     *
     * @throws InvalidStatusTransitionException
     */
    public function transitionTo(string $newStatus): self
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw InvalidStatusTransitionException::fromTo($this->status, $newStatus);
        }

        $this->status = $newStatus;
        $this->save();

        return $this;
    }

    /**
     * Check if ticket can transition to given status.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, $this->getValidNextStatuses());
    }

    /**
     * Get valid next statuses for current ticket state.
     */
    public function getValidNextStatuses(): array
    {
        return self::STATUS_TRANSITIONS[$this->status] ?? [];
    }

    // ============================================
    // Domain Logic - Assignment
    // ============================================

    /**
     * Assign ticket to a builder or admin.
     *
     * @throws InvalidTicketAssignmentException
     * @throws InvalidStatusTransitionException
     */
    public function assignTo(User $user): self
    {
        // Business rule: Only builders and admins can be assigned
        if (!$user->isBuilder() && !$user->isAdmin()) {
            throw InvalidTicketAssignmentException::userNotAuthorized($user->role);
        }

        // Business rule: Ticket must be in submitted status to be assigned
        if ($this->status !== 'submitted') {
            throw InvalidTicketAssignmentException::ticketNotInValidState($this->status);
        }

        $this->assigned_to = $user->id;
        $this->transitionTo('assigned');

        return $this;
    }

    /**
     * Reassign ticket to a different builder or admin.
     *
     * @throws InvalidTicketAssignmentException
     */
    public function reassignTo(User $user): self
    {
        // Business rule: Ticket must be assigned before reassignment
        if (!$this->isAssigned()) {
            throw new InvalidTicketAssignmentException('Ticket must be assigned before it can be reassigned');
        }

        // Business rule: Only builders and admins can be assigned
        if (!$user->isBuilder() && !$user->isAdmin()) {
            throw InvalidTicketAssignmentException::userNotAuthorized($user->role);
        }

        $this->assigned_to = $user->id;
        $this->save();

        return $this;
    }

    // ============================================
    // Domain Logic - Lifecycle Methods
    // ============================================

    /**
     * Start progress on the ticket.
     *
     * @throws InvalidStatusTransitionException
     */
    public function startProgress(): self
    {
        return $this->transitionTo('in_progress');
    }

    /**
     * Mark ticket as complete.
     *
     * @throws InvalidStatusTransitionException
     */
    public function markAsComplete(): self
    {
        return $this->transitionTo('complete');
    }

    /**
     * Close the ticket.
     *
     * @throws InvalidStatusTransitionException
     */
    public function close(): self
    {
        return $this->transitionTo('closed');
    }

    // ============================================
    // Domain Logic - Query Methods
    // ============================================

    /**
     * Check if ticket is open (not closed).
     */
    public function isOpen(): bool
    {
        return $this->status !== 'closed';
    }

    /**
     * Check if ticket is assigned to someone.
     */
    public function isAssigned(): bool
    {
        return $this->assigned_to !== null;
    }

    /**
     * Check if ticket is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if ticket is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }

    /**
     * Check if ticket is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
