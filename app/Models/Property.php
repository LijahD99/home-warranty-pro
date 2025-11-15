<?php

namespace App\Models;

use App\Exceptions\InvalidPropertyException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'city',
        'state',
        'zip_code',
        'notes',
    ];

    /**
     * Get the user that owns the property.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tickets for the property.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // ============================================
    // Domain Logic - Validation
    // ============================================

    /**
     * Validate the property data before saving.
     *
     * @throws InvalidPropertyException
     */
    public function validatePropertyData(): void
    {
        $this->validateZipCode($this->zip_code);
        $this->validateState($this->state);
    }

    /**
     * Validate ZIP code format.
     *
     * @throws InvalidPropertyException
     */
    protected function validateZipCode(string $zipCode): void
    {
        // US ZIP code: 5 digits or 5+4 format
        if (!preg_match('/^\d{5}(-\d{4})?$/', $zipCode)) {
            throw InvalidPropertyException::invalidZipCode($zipCode);
        }
    }

    /**
     * Validate US state code.
     *
     * @throws InvalidPropertyException
     */
    protected function validateState(string $state): void
    {
        if (!in_array(strtoupper($state), config('geo.us_states'))) {
            throw InvalidPropertyException::invalidState($state);
        }
    }

    // ============================================
    // Domain Logic - Ownership & Authorization
    // ============================================

    /**
     * Check if property is owned by the given user.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * Check if user can modify this property.
     */
    public function canBeModifiedBy(User $user): bool
    {
        // Admins can modify any property, owners can modify their own
        return $user->isAdmin() || $this->isOwnedBy($user);
    }

    /**
     * Verify ownership by user.
     *
     * @throws InvalidPropertyException
     */
    public function ensureOwnedBy(User $user): void
    {
        if (!$this->isOwnedBy($user) && !$user->isAdmin()) {
            throw InvalidPropertyException::notOwnedByUser();
        }
    }

    // ============================================
    // Domain Logic - Business Operations
    // ============================================

    /**
     * Check if property can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->getOpenTicketsCount() === 0;
    }

    /**
     * Ensure property can be deleted.
     *
     * @throws InvalidPropertyException
     */
    public function ensureCanBeDeleted(): void
    {
        $openTicketsCount = $this->getOpenTicketsCount();

        if ($openTicketsCount > 0) {
            throw InvalidPropertyException::hasOpenTickets($openTicketsCount);
        }
    }

    /**
     * Get count of open tickets for this property.
     */
    public function getOpenTicketsCount(): int
    {
        return $this->tickets()->whereIn('status', [
            'submitted',
            'assigned',
            'in_progress',
            'complete'
        ])->count();
    }

    /**
     * Get count of all tickets for this property.
     */
    public function getTicketsCount(): int
    {
        return $this->tickets()->count();
    }

    /**
     * Get full address as formatted string.
     */
    public function getFullAddress(): string
    {
        return sprintf(
            '%s, %s, %s %s',
            $this->address,
            $this->city,
            strtoupper($this->state),
            $this->zip_code
        );
    }

    /**
     * Check if property has any tickets.
     */
    public function hasTickets(): bool
    {
        return $this->tickets()->exists();
    }

    /**
     * Check if property has open tickets.
     */
    public function hasOpenTickets(): bool
    {
        return $this->getOpenTicketsCount() > 0;
    }

    /**
     * Update property details with validation.
     *
     * @throws InvalidPropertyException
     */
    public function updateDetails(array $details): self
    {
        foreach ($details as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->$key = $value;
            }
        }

        // Validate before persisting
        $this->validatePropertyData();

        // Save if valid
        $this->save();

        return $this;
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
        static::creating(function (Property $property) {
            $property->validatePropertyData();
        });

        // Validate before updating
        static::updating(function (Property $property) {
            $property->validatePropertyData();
        });
    }
}
