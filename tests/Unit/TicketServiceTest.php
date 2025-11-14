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
}
