<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => \App\Models\Property::factory(),
            'user_id' => \App\Models\User::factory(),
            'assigned_to' => null,
            'area_of_issue' => fake()->randomElement(['Kitchen', 'Bathroom', 'Living Room', 'Bedroom', 'Garage', 'Exterior']),
            'description' => fake()->paragraph(),
            'image_path' => null,
            'status' => 'submitted',
        ];
    }
}
