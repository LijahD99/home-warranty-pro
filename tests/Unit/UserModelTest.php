<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Property;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_check_if_user_is_homeowner(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);

        $this->assertTrue($homeowner->isHomeowner());
        $this->assertFalse($builder->isHomeowner());
    }

    /** @test */
    public function it_can_check_if_user_is_builder(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $this->assertTrue($builder->isBuilder());
        $this->assertFalse($homeowner->isBuilder());
    }

    /** @test */
    public function it_can_check_if_user_is_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($homeowner->isAdmin());
    }

    /** @test */
    public function it_can_check_if_user_has_specific_role(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $this->assertTrue($homeowner->hasRole(UserRole::HOMEOWNER));
        $this->assertFalse($homeowner->hasRole(UserRole::BUILDER));
    }

    /** @test */
    public function it_can_check_if_user_has_any_of_given_roles(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);

        $this->assertTrue($builder->hasAnyRole([UserRole::BUILDER, UserRole::ADMIN]));
        $this->assertFalse($builder->hasAnyRole([UserRole::HOMEOWNER, UserRole::ADMIN]));
    }

    /** @test */
    public function homeowner_can_create_properties(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $this->assertTrue($homeowner->role->canCreateProperties());
    }

    /** @test */
    public function admin_can_create_properties(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->assertTrue($admin->role->canCreateProperties());
    }

    /** @test */
    public function builder_cannot_create_properties(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);

        $this->assertFalse($builder->role->canCreateProperties());
    }

    /** @test */
    public function homeowner_can_create_tickets(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $this->assertTrue($homeowner->role->canCreateTickets());
    }

    /** @test */
    public function builder_cannot_create_tickets(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);

        $this->assertFalse($builder->role->canCreateTickets());
    }

    /** @test */
    public function builder_can_be_assigned_tickets(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);

        $this->assertTrue($builder->role->canBeAssignedTickets());
    }

    /** @test */
    public function admin_can_be_assigned_tickets(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->assertTrue($admin->role->canBeAssignedTickets());
    }

    /** @test */
    public function homeowner_cannot_be_assigned_tickets(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $this->assertFalse($homeowner->role->canBeAssignedTickets());
    }

    /** @test */
    public function builder_can_create_internal_comments(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);

        $this->assertTrue($builder->role->canCreateInternalComments());
    }

    /** @test */
    public function admin_can_create_internal_comments(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->assertTrue($admin->role->canCreateInternalComments());
    }

    /** @test */
    public function homeowner_cannot_create_internal_comments(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $this->assertFalse($homeowner->role->canCreateInternalComments());
    }

    /** @test */
    public function builder_can_view_all_tickets(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);

        $this->assertTrue($builder->role->canViewAllTickets());
    }

    /** @test */
    public function admin_can_view_all_tickets(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->assertTrue($admin->role->canViewAllTickets());
    }

    /** @test */
    public function homeowner_cannot_view_all_tickets(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $this->assertFalse($homeowner->role->canViewAllTickets());
    }

    /** @test */
    public function only_admin_can_manage_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $this->assertTrue($admin->role->canManageUsers());
        $this->assertFalse($builder->role->canManageUsers());
        $this->assertFalse($homeowner->role->canManageUsers());
    }

    /** @test */
    public function it_can_get_properties_count(): void
    {
        $user = User::factory()->create();
        Property::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertEquals(3, $user->getPropertiesCount());
    }

    /** @test */
    public function it_can_get_submitted_tickets_count(): void
    {
        $user = User::factory()->create();
        Ticket::factory()->count(5)->create(['user_id' => $user->id]);

        $this->assertEquals(5, $user->getSubmittedTicketsCount());
    }

    /** @test */
    public function it_can_get_assigned_tickets_count(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        Ticket::factory()->count(4)->create(['assigned_to' => $builder->id]);

        $this->assertEquals(4, $builder->getAssignedTicketsCount());
    }

    /** @test */
    public function it_can_get_open_assigned_tickets_count(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);

        Ticket::factory()->create(['assigned_to' => $builder->id, 'status' => 'assigned']);
        Ticket::factory()->create(['assigned_to' => $builder->id, 'status' => 'in_progress']);
        Ticket::factory()->create(['assigned_to' => $builder->id, 'status' => 'closed']);

        $this->assertEquals(2, $builder->getOpenAssignedTicketsCount());
    }

    /** @test */
    public function it_can_get_comments_count(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        Comment::createComment($ticket, $user, 'Comment 1');
        Comment::createComment($ticket, $user, 'Comment 2');

        $this->assertEquals(2, $user->getCommentsCount());
    }

    /** @test */
    public function it_can_check_if_user_has_properties(): void
    {
        $userWithProperty = User::factory()->create();
        $userWithoutProperty = User::factory()->create();

        Property::factory()->create(['user_id' => $userWithProperty->id]);

        $this->assertTrue($userWithProperty->hasProperties());
        $this->assertFalse($userWithoutProperty->hasProperties());
    }

    /** @test */
    public function it_can_check_if_user_has_open_tickets(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasOpenTickets());

        Ticket::factory()->create(['user_id' => $user->id, 'status' => 'submitted']);

        $this->assertTrue($user->fresh()->hasOpenTickets());
    }

    /** @test */
    public function it_does_not_count_closed_tickets_as_open(): void
    {
        $user = User::factory()->create();

        Ticket::factory()->create(['user_id' => $user->id, 'status' => 'closed']);

        $this->assertFalse($user->fresh()->hasOpenTickets());
    }

    /** @test */
    public function it_can_check_if_user_has_assigned_tickets(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);

        $this->assertFalse($builder->hasAssignedTickets());

        Ticket::factory()->create(['assigned_to' => $builder->id]);

        $this->assertTrue($builder->fresh()->hasAssignedTickets());
    }

    /** @test */
    public function it_can_get_role_display_name(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->assertEquals('Homeowner', $homeowner->getRoleDisplayName());
        $this->assertEquals('Builder/Manager', $builder->getRoleDisplayName());
        $this->assertEquals('Administrator', $admin->getRoleDisplayName());
    }

    /** @test */
    public function it_has_assigned_tickets_relationship(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $ticket = Ticket::factory()->create(['assigned_to' => $builder->id]);

        $assignedTickets = $builder->assignedTickets;

        $this->assertCount(1, $assignedTickets);
        $this->assertEquals($ticket->id, $assignedTickets->first()->id);
    }
}
