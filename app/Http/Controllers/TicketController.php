<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticket_service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Ticket::class);

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Homeowners see only their tickets
        if ($user->isHomeowner()) {
            $tickets = $user->tickets()
                ->with(['property', 'assignedTo'])
                ->latest()
                ->get();
        } else {
            // Builders and Admins see all tickets
            $tickets = Ticket::with(['property', 'user', 'assignedTo'])
                ->latest()
                ->get();
        }

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Ticket::class);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $properties = $user->properties;

        return view('tickets.create', compact('properties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Ticket::class);

        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'area_of_issue' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $validated['user_id'] = $user->id;

        $ticket = $this->ticket_service->createTicket($validated);

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket submitted successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['property', 'user', 'assignedTo', 'comments.user']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        return view('tickets.edit', compact('ticket'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        $validated = $request->validate([
            'status' => 'nullable|in:submitted,assigned,in_progress,complete,closed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (isset($validated['status'])) {
            $ticket->transitionTo($validated['status']);
        }

        if (isset($validated['assigned_to'])) {
            $builder = \App\Models\User::findOrFail($validated['assigned_to']);
            $ticket->assignTo($builder);
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        Gate::authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket deleted successfully!');
    }
}
