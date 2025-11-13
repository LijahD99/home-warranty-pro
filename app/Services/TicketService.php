<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    /**
     * Create a new ticket.
     */
    public function createTicket(array $data): Ticket
    {
        if (isset($data['image']) && $data['image']) {
            $data['image_path'] = $data['image']->store('tickets', 'public');
            unset($data['image']);
        }

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'submitted';
        }

        return Ticket::create($data);
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Ticket $ticket, string $new_status): Ticket
    {
        $allowed_transitions = [
            'submitted' => ['assigned'],
            'assigned' => ['in_progress'],
            'in_progress' => ['complete'],
            'complete' => ['closed'],
        ];

        $current_status = $ticket->status;

        if (!isset($allowed_transitions[$current_status]) ||
            !in_array($new_status, $allowed_transitions[$current_status])) {
            throw new \InvalidArgumentException(
                "Invalid status transition from {$current_status} to {$new_status}"
            );
        }

        $ticket->update(['status' => $new_status]);

        return $ticket->fresh();
    }

    /**
     * Assign ticket to a builder/manager.
     */
    public function assignTicket(Ticket $ticket, User $builder): Ticket
    {
        if (!$builder->isBuilder() && !$builder->isAdmin()) {
            throw new \InvalidArgumentException('User must be a builder or admin to be assigned tickets');
        }

        $ticket->update([
            'assigned_to' => $builder->id,
            'status' => 'assigned',
        ]);

        return $ticket->fresh();
    }

    /**
     * Upload and attach image to ticket.
     */
    public function uploadImage(Ticket $ticket, $image): Ticket
    {
        if ($ticket->image_path) {
            Storage::disk('public')->delete($ticket->image_path);
        }

        $image_path = $image->store('tickets', 'public');
        $ticket->update(['image_path' => $image_path]);

        return $ticket->fresh();
    }
}
