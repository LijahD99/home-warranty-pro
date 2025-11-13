<?php

namespace Tests\Unit;

use App\Models\Property;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TicketService $ticket_service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ticket_service = new TicketService();
    }

    public function test_can_create_ticket(): void
    {
        $user = User::factory()->create(['role' => 'homeowner']);
        $property = Property::factory()->create(['user_id' => $user->id]);

        $data = [
            'property_id' => $property->id,
            'user_id' => $user->id,
            'area_of_issue' => 'Kitchen',
            'description' => 'Leaking faucet',
        ];

        $ticket = $this->ticket_service->createTicket($data);

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertEquals('submitted', $ticket->status);
        $this->assertEquals('Kitchen', $ticket->area_of_issue);
    }

    public function test_can_update_ticket_status_with_valid_transition(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $updated_ticket = $this->ticket_service->updateStatus($ticket, 'assigned');

        $this->assertEquals('assigned', $updated_ticket->status);
    }

    public function test_cannot_update_ticket_status_with_invalid_transition(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        // Invalid transition: submitted -> complete (should go through assigned and in_progress first)
        $this->ticket_service->updateStatus($ticket, 'complete');
    }

    public function test_can_assign_ticket_to_builder(): void
    {
        $builder = User::factory()->create(['role' => 'builder']);
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $updated_ticket = $this->ticket_service->assignTicket($ticket, $builder);

        $this->assertEquals($builder->id, $updated_ticket->assigned_to);
        $this->assertEquals('assigned', $updated_ticket->status);
    }

    public function test_cannot_assign_ticket_to_homeowner(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $homeowner = User::factory()->create(['role' => 'homeowner']);
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $this->ticket_service->assignTicket($ticket, $homeowner);
    }

    public function test_status_transition_from_assigned_to_in_progress(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'assigned']);

        $updated_ticket = $this->ticket_service->updateStatus($ticket, 'in_progress');

        $this->assertEquals('in_progress', $updated_ticket->status);
    }

    public function test_status_transition_from_in_progress_to_complete(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_progress']);

        $updated_ticket = $this->ticket_service->updateStatus($ticket, 'complete');

        $this->assertEquals('complete', $updated_ticket->status);
    }

    public function test_status_transition_from_complete_to_closed(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'complete']);

        $updated_ticket = $this->ticket_service->updateStatus($ticket, 'closed');

        $this->assertEquals('closed', $updated_ticket->status);
    }
}
