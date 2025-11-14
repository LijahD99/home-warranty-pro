<?php

namespace Tests\Unit;

use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\InvalidTicketAssignmentException;
use App\Models\Property;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_transition_from_submitted_to_assigned(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $ticket->transitionTo('assigned');

        $this->assertEquals('assigned', $ticket->status);
    }

    /** @test */
    public function it_can_transition_from_assigned_to_in_progress(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'assigned']);

        $ticket->transitionTo('in_progress');

        $this->assertEquals('in_progress', $ticket->status);
    }

    /** @test */
    public function it_can_transition_from_in_progress_to_complete(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_progress']);

        $ticket->transitionTo('complete');

        $this->assertEquals('complete', $ticket->status);
    }

    /** @test */
    public function it_can_transition_from_complete_to_closed(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'complete']);

        $ticket->transitionTo('closed');

        $this->assertEquals('closed', $ticket->status);
    }

    /** @test */
    public function it_throws_exception_on_invalid_transition(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage("Invalid status transition from 'submitted' to 'complete'");

        $ticket = Ticket::factory()->create(['status' => 'submitted']);
        $ticket->transitionTo('complete');
    }

    /** @test */
    public function it_throws_exception_when_transitioning_from_closed(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $ticket = Ticket::factory()->create(['status' => 'closed']);
        $ticket->transitionTo('assigned');
    }

    /** @test */
    public function it_can_assign_to_builder(): void
    {
        $builder = User::factory()->create(['role' => 'builder']);
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $ticket->assignTo($builder);

        $this->assertEquals($builder->id, $ticket->assigned_to);
        $this->assertEquals('assigned', $ticket->status);
    }

    /** @test */
    public function it_can_assign_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $ticket->assignTo($admin);

        $this->assertEquals($admin->id, $ticket->assigned_to);
        $this->assertEquals('assigned', $ticket->status);
    }

    /** @test */
    public function it_cannot_assign_to_homeowner(): void
    {
        $this->expectException(InvalidTicketAssignmentException::class);
        $this->expectExceptionMessage("User with role 'homeowner' cannot be assigned tickets");

        $homeowner = User::factory()->create(['role' => 'homeowner']);
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $ticket->assignTo($homeowner);
    }

    /** @test */
    public function it_cannot_assign_ticket_that_is_not_submitted(): void
    {
        $this->expectException(InvalidTicketAssignmentException::class);
        $this->expectExceptionMessage("Ticket with status 'in_progress' cannot be assigned");

        $builder = User::factory()->create(['role' => 'builder']);
        $ticket = Ticket::factory()->create(['status' => 'in_progress']);

        $ticket->assignTo($builder);
    }

    /** @test */
    public function it_can_start_progress(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'assigned']);

        $ticket->startProgress();

        $this->assertEquals('in_progress', $ticket->status);
    }

    /** @test */
    public function it_cannot_start_progress_if_not_assigned(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $ticket = Ticket::factory()->create(['status' => 'submitted']);
        $ticket->startProgress();
    }

    /** @test */
    public function it_can_mark_as_complete(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_progress']);

        $ticket->markAsComplete();

        $this->assertEquals('complete', $ticket->status);
    }

    /** @test */
    public function it_cannot_mark_as_complete_if_not_in_progress(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $ticket = Ticket::factory()->create(['status' => 'submitted']);
        $ticket->markAsComplete();
    }

    /** @test */
    public function it_can_close_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'complete']);

        $ticket->close();

        $this->assertEquals('closed', $ticket->status);
    }

    /** @test */
    public function it_cannot_close_if_not_complete(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $ticket = Ticket::factory()->create(['status' => 'in_progress']);
        $ticket->close();
    }

    /** @test */
    public function it_can_check_if_ticket_is_open(): void
    {
        $openTicket = Ticket::factory()->create(['status' => 'submitted']);
        $closedTicket = Ticket::factory()->create(['status' => 'closed']);

        $this->assertTrue($openTicket->isOpen());
        $this->assertFalse($closedTicket->isOpen());
    }

    /** @test */
    public function it_can_check_if_ticket_is_assigned(): void
    {
        $builder = User::factory()->create(['role' => 'builder']);
        $assignedTicket = Ticket::factory()->create([
            'status' => 'assigned',
            'assigned_to' => $builder->id,
        ]);
        $unassignedTicket = Ticket::factory()->create([
            'status' => 'submitted',
            'assigned_to' => null,
        ]);

        $this->assertTrue($assignedTicket->isAssigned());
        $this->assertFalse($unassignedTicket->isAssigned());
    }

    /** @test */
    public function it_can_check_if_ticket_is_in_progress(): void
    {
        $inProgressTicket = Ticket::factory()->create(['status' => 'in_progress']);
        $submittedTicket = Ticket::factory()->create(['status' => 'submitted']);

        $this->assertTrue($inProgressTicket->isInProgress());
        $this->assertFalse($submittedTicket->isInProgress());
    }

    /** @test */
    public function it_can_check_if_ticket_is_complete(): void
    {
        $completeTicket = Ticket::factory()->create(['status' => 'complete']);
        $inProgressTicket = Ticket::factory()->create(['status' => 'in_progress']);

        $this->assertTrue($completeTicket->isComplete());
        $this->assertFalse($inProgressTicket->isComplete());
    }

    /** @test */
    public function it_can_get_valid_next_statuses(): void
    {
        $submittedTicket = Ticket::factory()->create(['status' => 'submitted']);
        $assignedTicket = Ticket::factory()->create(['status' => 'assigned']);
        $closedTicket = Ticket::factory()->create(['status' => 'closed']);

        $this->assertEquals(['assigned'], $submittedTicket->getValidNextStatuses());
        $this->assertEquals(['in_progress'], $assignedTicket->getValidNextStatuses());
        $this->assertEquals([], $closedTicket->getValidNextStatuses());
    }

    /** @test */
    public function it_can_check_if_status_transition_is_valid(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $this->assertTrue($ticket->canTransitionTo('assigned'));
        $this->assertFalse($ticket->canTransitionTo('in_progress'));
        $this->assertFalse($ticket->canTransitionTo('complete'));
    }

    /** @test */
    public function it_can_reassign_to_different_builder(): void
    {
        $builder1 = User::factory()->create(['role' => 'builder']);
        $builder2 = User::factory()->create(['role' => 'builder']);

        $ticket = Ticket::factory()->create([
            'status' => 'assigned',
            'assigned_to' => $builder1->id,
        ]);

        $ticket->reassignTo($builder2);

        $this->assertEquals($builder2->id, $ticket->assigned_to);
        $this->assertEquals('assigned', $ticket->status);
    }

    /** @test */
    public function it_cannot_reassign_unassigned_ticket(): void
    {
        $this->expectException(InvalidTicketAssignmentException::class);
        $this->expectExceptionMessage('Ticket must be assigned before it can be reassigned');

        $builder = User::factory()->create(['role' => 'builder']);
        $ticket = Ticket::factory()->create(['status' => 'submitted']);

        $ticket->reassignTo($builder);
    }
}
