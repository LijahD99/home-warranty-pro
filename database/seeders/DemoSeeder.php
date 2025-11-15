<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Property;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@homewarranty.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);

        // Create Builder/Manager Users
        $builder1 = User::create([
            'name' => 'John Builder',
            'email' => 'builder1@homewarranty.com',
            'password' => Hash::make('password'),
            'role' => UserRole::BUILDER,
        ]);

        $builder2 = User::create([
            'name' => 'Sarah Manager',
            'email' => 'builder2@homewarranty.com',
            'password' => Hash::make('password'),
            'role' => UserRole::BUILDER,
        ]);

        // Create Homeowner Users with Properties and Tickets
        $homeowner1 = User::create([
            'name' => 'Michael Thompson',
            'email' => 'homeowner1@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::HOMEOWNER,
        ]);

        $property1 = Property::create([
            'user_id' => $homeowner1->id,
            'address' => '123 Maple Street',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip_code' => '62701',
            'notes' => 'Single family home built in 2020. Two-story with attached garage.',
        ]);

        $property2 = Property::create([
            'user_id' => $homeowner1->id,
            'address' => '456 Oak Avenue',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip_code' => '62704',
            'notes' => 'Ranch style home with finished basement.',
        ]);

        $homeowner2 = User::create([
            'name' => 'Jennifer Martinez',
            'email' => 'homeowner2@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::HOMEOWNER,
        ]);

        $property3 = Property::create([
            'user_id' => $homeowner2->id,
            'address' => '789 Pine Lane',
            'city' => 'Chicago',
            'state' => 'IL',
            'zip_code' => '60601',
            'notes' => 'Modern condo on 15th floor. Great city views.',
        ]);

        $property4 = Property::create([
            'user_id' => $homeowner2->id,
            'address' => '321 Birch Road',
            'city' => 'Naperville',
            'state' => 'IL',
            'zip_code' => '60540',
        ]);

        $homeowner3 = User::create([
            'name' => 'David Wilson',
            'email' => 'homeowner3@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::HOMEOWNER,
        ]);

        $property5 = Property::create([
            'user_id' => $homeowner3->id,
            'address' => '555 Elm Street',
            'city' => 'Aurora',
            'state' => 'IL',
            'zip_code' => '60502',
            'notes' => 'Colonial style with 4 bedrooms. Recently renovated kitchen.',
        ]);

        // Create Tickets with varying statuses

        // Ticket 1: Submitted (just created)
        $ticket1 = Ticket::create([
            'user_id' => $homeowner1->id,
            'property_id' => $property1->id,
            'area_of_issue' => 'HVAC System',
            'description' => 'Air conditioning unit not cooling properly. Temperature stays around 75°F even when set to 68°F. Issue started 2 days ago.',
            'status' => 'submitted',
        ]);

        Comment::create([
            'ticket_id' => $ticket1->id,
            'user_id' => $homeowner1->id,
            'comment' => 'The AC has been running constantly but not cooling the house down.',
            'is_internal' => false,
        ]);

        // Ticket 2: Assigned to builder
        $ticket2 = Ticket::create([
            'user_id' => $homeowner1->id,
            'property_id' => $property1->id,
            'area_of_issue' => 'Kitchen Plumbing',
            'description' => 'Kitchen sink faucet is dripping constantly. Wasting a lot of water and making noise at night.',
            'status' => 'assigned',
            'assigned_to' => $builder1->id,
        ]);

        Comment::create([
            'ticket_id' => $ticket2->id,
            'user_id' => $builder1->id,
            'comment' => 'I will schedule a visit this week to inspect the faucet. Likely needs a new cartridge.',
            'is_internal' => false,
        ]);

        Comment::create([
            'ticket_id' => $ticket2->id,
            'user_id' => $builder1->id,
            'comment' => 'Ordered replacement parts. Will arrive in 2 days.',
            'is_internal' => true,
        ]);

        // Ticket 3: In Progress
        $ticket3 = Ticket::create([
            'user_id' => $homeowner2->id,
            'property_id' => $property3->id,
            'area_of_issue' => 'Electrical',
            'description' => 'Master bedroom outlet not working. Tried resetting circuit breaker but issue persists.',
            'status' => 'in_progress',
            'assigned_to' => $builder2->id,
        ]);

        Comment::create([
            'ticket_id' => $ticket3->id,
            'user_id' => $builder2->id,
            'comment' => 'Electrician will be on-site tomorrow to diagnose the issue.',
            'is_internal' => false,
        ]);

        Comment::create([
            'ticket_id' => $ticket3->id,
            'user_id' => $homeowner2->id,
            'comment' => 'Great! I will be home after 2 PM.',
            'is_internal' => false,
        ]);

        Comment::create([
            'ticket_id' => $ticket3->id,
            'user_id' => $builder2->id,
            'comment' => 'Electrician found the issue - faulty GFCI outlet. Replacement needed.',
            'is_internal' => true,
        ]);

        // Ticket 4: Complete
        $ticket4 = Ticket::create([
            'user_id' => $homeowner2->id,
            'property_id' => $property3->id,
            'area_of_issue' => 'Windows',
            'description' => 'Living room window not closing properly. Gap causing draft and noise.',
            'status' => 'complete',
            'assigned_to' => $builder1->id,
        ]);

        Comment::create([
            'ticket_id' => $ticket4->id,
            'user_id' => $builder1->id,
            'comment' => 'Window latch was misaligned. Adjusted and tested - working properly now.',
            'is_internal' => false,
        ]);

        Comment::create([
            'ticket_id' => $ticket4->id,
            'user_id' => $homeowner2->id,
            'comment' => 'Thank you! Window is working great now.',
            'is_internal' => false,
        ]);

        // Ticket 5: Closed
        $ticket5 = Ticket::create([
            'user_id' => $homeowner3->id,
            'property_id' => $property5->id,
            'area_of_issue' => 'Garage Door',
            'description' => 'Garage door opener remote stopped working. Tried replacing batteries but no change.',
            'status' => 'closed',
            'assigned_to' => $builder2->id,
        ]);

        Comment::create([
            'ticket_id' => $ticket5->id,
            'user_id' => $builder2->id,
            'comment' => 'Reprogrammed the remote. It is working now.',
            'is_internal' => false,
        ]);

        Comment::create([
            'ticket_id' => $ticket5->id,
            'user_id' => $homeowner3->id,
            'comment' => 'Perfect! Thank you for the quick fix.',
            'is_internal' => false,
        ]);

        // Ticket 6: Submitted
        $ticket6 = Ticket::create([
            'user_id' => $homeowner1->id,
            'property_id' => $property2->id,
            'area_of_issue' => 'Roof',
            'description' => 'Small leak in the ceiling near the bathroom. Appears to be coming from the roof.',
            'status' => 'submitted',
        ]);

        // Ticket 7: Assigned
        $ticket7 = Ticket::create([
            'user_id' => $homeowner3->id,
            'property_id' => $property5->id,
            'area_of_issue' => 'Flooring',
            'description' => 'Hardwood floor in hallway has started to warp. Likely due to water damage.',
            'status' => 'assigned',
            'assigned_to' => $builder1->id,
        ]);

        Comment::create([
            'ticket_id' => $ticket7->id,
            'user_id' => $builder1->id,
            'comment' => 'Need to identify source of moisture first. Will inspect this week.',
            'is_internal' => true,
        ]);

        // Ticket 8: In Progress
        $ticket8 = Ticket::create([
            'user_id' => $homeowner2->id,
            'property_id' => $property4->id,
            'area_of_issue' => 'Water Heater',
            'description' => 'Hot water not lasting as long as it used to. Water heater making strange noises.',
            'status' => 'in_progress',
            'assigned_to' => $builder2->id,
        ]);

        Comment::create([
            'ticket_id' => $ticket8->id,
            'user_id' => $builder2->id,
            'comment' => 'Sediment buildup in tank. Draining and flushing the water heater.',
            'is_internal' => false,
        ]);

        // Ticket 9: Submitted
        $ticket9 = Ticket::create([
            'user_id' => $homeowner1->id,
            'property_id' => $property1->id,
            'area_of_issue' => 'Bathroom',
            'description' => 'Guest bathroom toilet runs continuously after flushing. Handle needs to be jiggled to stop.',
            'status' => 'submitted',
        ]);

        // Ticket 10: Complete
        $ticket10 = Ticket::create([
            'user_id' => $homeowner3->id,
            'property_id' => $property5->id,
            'area_of_issue' => 'Appliances',
            'description' => 'Dishwasher not draining properly. Water pools at the bottom after cycle.',
            'status' => 'complete',
            'assigned_to' => $builder1->id,
        ]);

        Comment::create([
            'ticket_id' => $ticket10->id,
            'user_id' => $builder1->id,
            'comment' => 'Cleared clog in drain hose. Dishwasher draining properly now.',
            'is_internal' => false,
        ]);

        Comment::create([
            'ticket_id' => $ticket10->id,
            'user_id' => $homeowner3->id,
            'comment' => 'Works perfectly! Thanks!',
            'is_internal' => false,
        ]);

        // Ticket 11: Assigned
        $ticket11 = Ticket::create([
            'user_id' => $homeowner2->id,
            'property_id' => $property3->id,
            'area_of_issue' => 'Thermostat',
            'description' => 'Thermostat display is blank. Heating system not responding.',
            'status' => 'assigned',
            'assigned_to' => $builder2->id,
        ]);

        // Ticket 12: In Progress
        $ticket12 = Ticket::create([
            'user_id' => $homeowner1->id,
            'property_id' => $property2->id,
            'area_of_issue' => 'Foundation',
            'description' => 'Small crack appeared in basement wall. Concerned about structural integrity.',
            'status' => 'in_progress',
            'assigned_to' => $builder1->id,
        ]);

        Comment::create([
            'ticket_id' => $ticket12->id,
            'user_id' => $builder1->id,
            'comment' => 'Structural engineer will inspect the crack next week to determine if it is a concern.',
            'is_internal' => false,
        ]);

        Comment::create([
            'ticket_id' => $ticket12->id,
            'user_id' => $builder1->id,
            'comment' => 'Scheduled engineer for Tuesday at 10 AM.',
            'is_internal' => true,
        ]);

        Comment::create([
            'ticket_id' => $ticket12->id,
            'user_id' => $homeowner1->id,
            'comment' => 'I will be available. Please let me know if you need anything else.',
            'is_internal' => false,
        ]);

        // Ticket 13: Submitted
        $ticket13 = Ticket::create([
            'user_id' => $homeowner2->id,
            'property_id' => $property4->id,
            'area_of_issue' => 'Exterior',
            'description' => 'Siding coming loose on east side of house. Flapping in the wind.',
            'status' => 'submitted',
        ]);

        // Ticket 14: Closed
        $ticket14 = Ticket::create([
            'user_id' => $homeowner1->id,
            'property_id' => $property1->id,
            'area_of_issue' => 'Landscaping',
            'description' => 'Sprinkler system zone 3 not activating. Other zones work fine.',
            'status' => 'closed',
            'assigned_to' => $builder2->id,
        ]);

        Comment::create([
            'ticket_id' => $ticket14->id,
            'user_id' => $builder2->id,
            'comment' => 'Replaced faulty solenoid valve. All zones working now.',
            'is_internal' => false,
        ]);

        // Ticket 15: Complete
        $ticket15 = Ticket::create([
            'user_id' => $homeowner3->id,
            'property_id' => $property5->id,
            'area_of_issue' => 'Deck',
            'description' => 'Several deck boards are loose and creaking. Safety concern.',
            'status' => 'complete',
            'assigned_to' => $builder1->id,
        ]);

        Comment::create([
            'ticket_id' => $ticket15->id,
            'user_id' => $builder1->id,
            'comment' => 'Replaced 4 damaged boards and reinforced the deck structure. All secure now.',
            'is_internal' => false,
        ]);

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@homewarranty.com / password');
        $this->command->info('Builder 1: builder1@homewarranty.com / password');
        $this->command->info('Builder 2: builder2@homewarranty.com / password');
        $this->command->info('Homeowner 1: homeowner1@example.com / password');
        $this->command->info('Homeowner 2: homeowner2@example.com / password');
        $this->command->info('Homeowner 3: homeowner3@example.com / password');
    }
}
