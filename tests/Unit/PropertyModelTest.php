<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Exceptions\InvalidPropertyException;
use App\Models\Property;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PropertyModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_zip_code_on_creation(): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Invalid ZIP code format: 1234');

        $user = User::factory()->create();
        Property::create([
            'user_id' => $user->id,
            'address' => '123 Main St',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '1234', // Invalid: too short
        ]);
    }

    #[Test]
    public function it_accepts_valid_five_digit_zip_code(): void
    {
        $user = User::factory()->create();
        $property = Property::create([
            'user_id' => $user->id,
            'address' => '123 Main St',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '78701',
        ]);

        $this->assertEquals('78701', $property->zip_code);
    }

    #[Test]
    public function it_accepts_valid_zip_plus_four_format(): void
    {
        $user = User::factory()->create();
        $property = Property::create([
            'user_id' => $user->id,
            'address' => '123 Main St',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '78701-1234',
        ]);

        $this->assertEquals('78701-1234', $property->zip_code);
    }

    #[Test]
    public function it_validates_state_code_on_creation(): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Invalid state code: XX');

        $user = User::factory()->create();
        Property::create([
            'user_id' => $user->id,
            'address' => '123 Main St',
            'city' => 'Austin',
            'state' => 'XX', // Invalid state
            'zip_code' => '78701',
        ]);
    }

    #[Test]
    public function it_accepts_all_valid_us_state_codes(): void
    {
        $user = User::factory()->create();

        $validStates = config('geo.us_states');

        foreach ($validStates as $state) {
            $property = Property::create([
                'user_id' => $user->id,
                'address' => '123 Main St',
                'city' => 'Test City',
                'state' => $state,
                'zip_code' => '12345',
            ]);

            $this->assertEquals(strtoupper($state), strtoupper($property->state));
        }
    }

    #[Test]
    public function it_can_check_if_owned_by_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($property->isOwnedBy($owner));
        $this->assertFalse($property->isOwnedBy($otherUser));
    }

    #[Test]
    public function it_allows_owner_to_modify_property(): void
    {
        $owner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($property->canBeModifiedBy($owner));
    }

    #[Test]
    public function it_allows_admin_to_modify_any_property(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $owner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($property->canBeModifiedBy($admin));
    }

    #[Test]
    public function it_prevents_non_owner_from_modifying_property(): void
    {
        $owner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $otherUser = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($property->canBeModifiedBy($otherUser));
    }

    #[Test]
    public function it_ensures_property_is_owned_by_user(): void
    {
        $owner = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $property->ensureOwnedBy($owner);
        $this->assertTrue(true); // No exception thrown
    }

    #[Test]
    public function it_throws_exception_when_ensuring_ownership_fails(): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Cannot perform this action on a property you do not own');

        $owner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $otherUser = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $property->ensureOwnedBy($otherUser);
    }

    #[Test]
    public function it_allows_admin_when_ensuring_ownership(): void
    {
        $owner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $property->ensureOwnedBy($admin);
        $this->assertTrue(true); // No exception thrown
    }

    #[Test]
    public function it_can_check_if_property_has_tickets(): void
    {
        $property = Property::factory()->create();

        $this->assertFalse($property->hasTickets());

        Ticket::factory()->create(['property_id' => $property->id]);

        $this->assertTrue($property->fresh()->hasTickets());
    }

    #[Test]
    public function it_can_get_tickets_count(): void
    {
        $property = Property::factory()->create();
        Ticket::factory()->count(3)->create(['property_id' => $property->id]);

        $this->assertEquals(3, $property->fresh()->getTicketsCount());
    }

    #[Test]
    public function it_can_check_if_property_has_open_tickets(): void
    {
        $property = Property::factory()->create();

        $this->assertFalse($property->hasOpenTickets());

        Ticket::factory()->create([
            'property_id' => $property->id,
            'status' => 'submitted'
        ]);

        $this->assertTrue($property->fresh()->hasOpenTickets());
    }

    #[Test]
    public function it_does_not_count_closed_tickets_as_open(): void
    {
        $property = Property::factory()->create();

        Ticket::factory()->create([
            'property_id' => $property->id,
            'status' => 'closed'
        ]);

        $this->assertFalse($property->fresh()->hasOpenTickets());
        $this->assertEquals(0, $property->fresh()->getOpenTicketsCount());
    }

    #[Test]
    public function it_can_get_open_tickets_count(): void
    {
        $property = Property::factory()->create();

        Ticket::factory()->create(['property_id' => $property->id, 'status' => 'submitted']);
        Ticket::factory()->create(['property_id' => $property->id, 'status' => 'in_progress']);
        Ticket::factory()->create(['property_id' => $property->id, 'status' => 'closed']);

        $this->assertEquals(2, $property->fresh()->getOpenTicketsCount());
    }

    #[Test]
    public function it_can_be_deleted_when_no_open_tickets(): void
    {
        $property = Property::factory()->create();

        $this->assertTrue($property->canBeDeleted());
    }

    #[Test]
    public function it_cannot_be_deleted_when_has_open_tickets(): void
    {
        $property = Property::factory()->create();
        Ticket::factory()->create([
            'property_id' => $property->id,
            'status' => 'submitted'
        ]);

        $this->assertFalse($property->fresh()->canBeDeleted());
    }

    #[Test]
    public function it_ensures_property_can_be_deleted(): void
    {
        $property = Property::factory()->create();

        $property->ensureCanBeDeleted();
        $this->assertTrue(true); // No exception thrown
    }

    #[Test]
    public function it_throws_exception_when_deleting_property_with_open_tickets(): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Cannot delete property with 2 open ticket(s)');

        $property = Property::factory()->create();
        Ticket::factory()->count(2)->create([
            'property_id' => $property->id,
            'status' => 'submitted'
        ]);

        $property->fresh()->ensureCanBeDeleted();
    }

    #[Test]
    public function it_can_be_deleted_when_only_closed_tickets_exist(): void
    {
        $property = Property::factory()->create();
        Ticket::factory()->count(3)->create([
            'property_id' => $property->id,
            'status' => 'closed'
        ]);

        $this->assertTrue($property->fresh()->canBeDeleted());
    }

    #[Test]
    public function it_can_get_full_address(): void
    {
        $property = Property::factory()->create([
            'address' => '123 Main St',
            'city' => 'Austin',
            'state' => 'tx',
            'zip_code' => '78701',
        ]);

        $this->assertEquals('123 Main St, Austin, TX 78701', $property->getFullAddress());
    }

    #[Test]
    public function it_can_update_details_with_validation(): void
    {
        $property = Property::factory()->create([
            'address' => '123 Main St',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '78701',
        ]);

        $property->updateDetails([
            'address' => '456 Oak Ave',
            'city' => 'Dallas',
            'zip_code' => '75201',
        ]);

        $this->assertEquals('456 Oak Ave', $property->address);
        $this->assertEquals('Dallas', $property->city);
        $this->assertEquals('75201', $property->zip_code);
    }

    #[Test]
    public function it_validates_when_updating_details(): void
    {
        $this->expectException(InvalidPropertyException::class);

        $property = Property::factory()->create([
            'address' => '123 Main St',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '78701',
        ]);

        $property->updateDetails([
            'zip_code' => 'invalid',
        ]);
    }

    #[Test]
    public function it_validates_on_update(): void
    {
        $this->expectException(InvalidPropertyException::class);

        $property = Property::factory()->create();
        $property->state = 'INVALID';
        $property->save();
    }
}
