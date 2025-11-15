<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Property;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function homeowner_can_create_ticket_for_their_property(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);

        $response = $this->actingAs($homeowner)->post(route('tickets.store'), [
            'property_id' => $property->id,
            'area_of_issue' => 'Kitchen',
            'description' => 'The faucet is leaking badly and needs immediate attention.',
        ]);

        $response->assertRedirect(route('tickets.index'));
        $this->assertDatabaseHas('tickets', [
            'property_id' => $property->id,
            'user_id' => $homeowner->id,
            'area_of_issue' => 'Kitchen',
            'status' => 'submitted',
        ]);
    }

    #[Test]
    public function homeowner_can_view_their_tickets(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $homeowner->id,
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($homeowner)->get(route('tickets.index'));

        $response->assertStatus(200);
        $response->assertSee($ticket->area_of_issue);
    }

    #[Test]
    public function homeowner_cannot_view_other_homeowners_tickets(): void
    {
        $homeowner1 = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $homeowner2 = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner2->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $homeowner2->id,
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($homeowner1)->get(route('tickets.show', $ticket));

        $response->assertStatus(403);
    }

    #[Test]
    public function builder_can_view_all_tickets(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $homeowner->id,
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($builder)->get(route('tickets.index'));

        $response->assertStatus(200);
        $response->assertSee($ticket->area_of_issue);
    }

    #[Test]
    public function builder_can_assign_ticket_to_themselves(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $homeowner->id,
            'property_id' => $property->id,
            'status' => 'submitted',
        ]);

        $ticket->assignTo($builder);

        $this->assertEquals($builder->id, $ticket->fresh()->assigned_to);
        $this->assertEquals('assigned', $ticket->fresh()->status);
    }

    #[Test]
    public function ticket_status_transitions_follow_workflow(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $homeowner->id,
            'property_id' => $property->id,
            'status' => 'submitted',
        ]);

        // Can transition to assigned
        $ticket->transitionTo('assigned');
        $this->assertEquals('assigned', $ticket->status);

        // Can transition to in_progress
        $ticket->transitionTo('in_progress');
        $this->assertEquals('in_progress', $ticket->status);

        // Can transition to complete
        $ticket->transitionTo('complete');
        $this->assertEquals('complete', $ticket->status);

        // Can transition to closed
        $ticket->transitionTo('closed');
        $this->assertEquals('closed', $ticket->status);
    }

    #[Test]
    public function ticket_cannot_transition_to_invalid_status(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $homeowner->id,
            'property_id' => $property->id,
            'status' => 'submitted',
        ]);

        $this->expectException(\App\Exceptions\InvalidStatusTransitionException::class);

        // Cannot go directly from submitted to closed
        $ticket->transitionTo('closed');
    }

    #[Test]
    public function homeowner_can_add_public_comment_to_their_ticket(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $homeowner->id,
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($homeowner)->post(route('comments.store', $ticket), [
            'comment' => 'This is my comment on the ticket.',
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $homeowner->id,
            'comment' => 'This is my comment on the ticket.',
            'is_internal' => false,
        ]);
    }

    #[Test]
    public function homeowner_cannot_add_internal_comments(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $homeowner->id,
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($homeowner)->post(route('comments.store', $ticket), [
            'comment' => 'Trying to add internal comment',
            'is_internal' => true,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));

        // Comment should be created but is_internal should be false
        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'comment' => 'Trying to add internal comment',
            'is_internal' => false,
        ]);
    }

    #[Test]
    public function builder_can_add_internal_comments(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $homeowner->id,
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($builder)->post(route('comments.store', $ticket), [
            'comment' => 'Internal note for team only',
            'is_internal' => true,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $builder->id,
            'comment' => 'Internal note for team only',
            'is_internal' => true,
        ]);
    }

    #[Test]
    public function ticket_validation_requires_all_fields(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);

        $response = $this->actingAs($homeowner)->post(route('tickets.store'), [
            'property_id' => '',
            'area_of_issue' => '',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['property_id', 'area_of_issue', 'description']);
    }

    #[Test]
    public function ticket_description_must_be_at_least_10_characters(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);

        $response = $this->actingAs($homeowner)->post(route('tickets.store'), [
            'property_id' => $property->id,
            'area_of_issue' => 'Kitchen',
            'description' => 'Short',
        ]);

        $response->assertSessionHasErrors(['description']);
    }
}
