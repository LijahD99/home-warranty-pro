<?php

namespace App\Services;

use App\Models\Ticket;
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

