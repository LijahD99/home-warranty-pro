<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function homeowner_can_view_their_properties(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);

        $response = $this->actingAs($homeowner)->get(route('properties.index'));

        $response->assertStatus(200);
        $response->assertSee($property->address);
    }

    #[Test]
    public function homeowner_can_create_property(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $response = $this->actingAs($homeowner)->post(route('properties.store'), [
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'IL',
            'zip_code' => '12345',
            'notes' => 'Test property',
        ]);

        $response->assertRedirect(route('properties.index'));
        $this->assertDatabaseHas('properties', [
            'address' => '123 Test Street',
            'user_id' => $homeowner->id,
        ]);
    }

    #[Test]
    public function homeowner_can_update_their_property(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);

        $response = $this->actingAs($homeowner)->put(route('properties.update', $property), [
            'address' => 'Updated Address',
            'city' => $property->city,
            'state' => $property->state,
            'zip_code' => $property->zip_code,
        ]);

        $response->assertRedirect(route('properties.index'));
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'address' => 'Updated Address',
        ]);
    }

    #[Test]
    public function homeowner_can_delete_property_without_tickets(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);

        $response = $this->actingAs($homeowner)->delete(route('properties.destroy', $property));

        $response->assertRedirect(route('properties.index'));
        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }

    #[Test]
    public function homeowner_cannot_view_other_homeowners_properties(): void
    {
        $homeowner1 = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $homeowner2 = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner2->id]);

        $response = $this->actingAs($homeowner1)->get(route('properties.show', $property));

        $response->assertStatus(403);
    }

    #[Test]
    public function builder_cannot_create_properties(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);

        $response = $this->actingAs($builder)->get(route('properties.create'));

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_view_all_properties(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $property = Property::factory()->create(['user_id' => $homeowner->id]);

        $response = $this->actingAs($admin)->get(route('properties.show', $property));

        $response->assertStatus(200);
        $response->assertSee($property->address);
    }

    #[Test]
    public function property_validation_requires_all_fields(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $response = $this->actingAs($homeowner)->post(route('properties.store'), [
            'address' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
        ]);

        $response->assertSessionHasErrors(['address', 'city', 'state', 'zip_code']);
    }

    #[Test]
    public function property_validation_requires_valid_zip_code(): void
    {
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);

        $response = $this->actingAs($homeowner)->post(route('properties.store'), [
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'IL',
            'zip_code' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['zip_code']);
    }
}
