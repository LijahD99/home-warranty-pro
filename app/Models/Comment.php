<?php

namespace App\Models;

use App\Exceptions\InvalidCommentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the ticket that owns the comment.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user who made the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ============================================
    // Domain Logic - Validation
    // ============================================

    /**
     * Validate comment data.
     *
     * @throws InvalidCommentException
     */
    public function validateCommentData(): void
    {
        if (empty(trim($this->comment))) {
            throw InvalidCommentException::emptyComment();
        }
    }

    /**
     * Validate if user can create internal comment.
     *
     * @throws InvalidCommentException
     */
    public function validateInternalCommentPermission(User $user): void
    {
        if ($this->is_internal && !$user->isBuilder() && !$user->isAdmin()) {
            throw InvalidCommentException::cannotMarkAsInternal($user->role);
        }
    }

    // ============================================
    // Domain Logic - Authorization
    // ============================================

    /**
     * Check if comment was made by the given user.
     */
    public function isAuthoredBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * Check if user can edit this comment.
     */
    public function canBeEditedBy(User $user): bool
    {
        // Only the author or an admin can edit
        return $this->isAuthoredBy($user) || $user->isAdmin();
    }

    /**
     * Check if user can delete this comment.
     */
    public function canBeDeletedBy(User $user): bool
    {
        // Only the author or an admin can delete
        return $this->isAuthoredBy($user) || $user->isAdmin();
    }

    /**
     * Check if user can view this comment.
     */
    public function canBeViewedBy(User $user): bool
    {
        // Internal comments can only be viewed by builders and admins
        if ($this->is_internal) {
            return $user->isBuilder() || $user->isAdmin();
        }

        // Non-internal comments can be viewed by anyone involved with the ticket
        return true;
    }

    /**
     * Ensure user can edit this comment.
     *
     * @throws InvalidCommentException
     */
    public function ensureCanBeEditedBy(User $user): void
    {
        if (!$this->canBeEditedBy($user)) {
            throw InvalidCommentException::notAuthorized('Only the comment author or an admin can edit this comment');
        }
    }

    /**
     * Ensure user can delete this comment.
     *
     * @throws InvalidCommentException
     */
    public function ensureCanBeDeletedBy(User $user): void
    {
        if (!$this->canBeDeletedBy($user)) {
            throw InvalidCommentException::notAuthorized('Only the comment author or an admin can delete this comment');
        }
    }

    // ============================================
    // Domain Logic - Business Operations
    // ============================================

    /**
     * Create a new comment with validation.
     *
     * @throws InvalidCommentException
     */
    public static function createComment(Ticket $ticket, User $user, string $text, bool $isInternal = false): self
    {
        $comment = new self([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => $text,
            'is_internal' => $isInternal,
        ]);

        $comment->validateCommentData();
        $comment->validateInternalCommentPermission($user);

        $comment->save();

        return $comment;
    }

    /**
     * Update comment text.
     *
     * @throws InvalidCommentException
     */
    public function updateText(string $newText, User $user): self
    {
        $this->ensureCanBeEditedBy($user);

        $this->comment = $newText;
        $this->validateCommentData();
        $this->save();

        return $this;
    }

    /**
     * Mark comment as internal.
     *
     * @throws InvalidCommentException
     */
    public function markAsInternal(User $user): self
    {
        $this->ensureCanBeEditedBy($user);

        if (!$user->isBuilder() && !$user->isAdmin()) {
            throw InvalidCommentException::cannotMarkAsInternal($user->role);
        }

        $this->is_internal = true;
        $this->save();

        return $this;
    }

    /**
     * Mark comment as public (non-internal).
     *
     * @throws InvalidCommentException
     */
    public function markAsPublic(User $user): self
    {
        $this->ensureCanBeEditedBy($user);

        $this->is_internal = false;
        $this->save();

        return $this;
    }

    // ============================================
    // Domain Logic - Query Methods
    // ============================================

    /**
     * Check if comment is internal.
     */
    public function isInternal(): bool
    {
        return $this->is_internal === true;
    }

    /**
     * Check if comment is public.
     */
    public function isPublic(): bool
    {
        return !$this->isInternal();
    }

    /**
     * Get formatted timestamp.
     */
    public function getFormattedTimestamp(): string
    {
        return $this->created_at->diffForHumans();
    }

    // ============================================
    // Model Events
    // ============================================

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Validate before creating
        static::creating(function (Comment $comment) {
            $comment->validateCommentData();
        });

        // Validate before updating
        static::updating(function (Comment $comment) {
            $comment->validateCommentData();
        });
    }
}
