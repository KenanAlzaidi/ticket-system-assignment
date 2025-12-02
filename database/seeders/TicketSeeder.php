<?php

namespace Database\Seeders;

use App\Models\AdminNote;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all department connections
        $departments = config('departments.connection_map');
        $departmentCount = count($departments);
        
        if ($departmentCount === 0) {
            $this->command->warn('No departments configured. Skipping ticket seeding.');
            return;
        }

        // Distribute 1000 tickets across the departments
        $totalTickets = 1000;
        
        $baseCount = floor($totalTickets / $departmentCount);
        $remainder = $totalTickets % $departmentCount;

        $this->command->info("Seeding {$totalTickets} tickets across {$departmentCount} departments...");

        $iteration = 0;
        foreach ($departments as $departmentName => $connectionName) {
            // Add 1 to the count if we still have remainders to distribute
            $countForThisDept = $baseCount + ($iteration < $remainder ? 1 : 0);
            $iteration++;
            
            $this->command->info("Creating {$countForThisDept} tickets for: {$departmentName} ({$connectionName})");

            try {
                // Verify connection works before attempting seeding
                \Illuminate\Support\Facades\DB::connection($connectionName)->getPdo();
            } catch (\Exception $e) {
                $this->command->error("Skipping {$departmentName}: Could not connect to database.");
                continue;
            }

            // Note: We cannot use batch insert easily if we need to get IDs back for AdminNotes relation.
            // To support 'noted' status logic correctly, we will create them one by one (or batches with logic)
            // creating one by one is safer for relational integrity in seeders.
            
            for ($i = 0; $i < $countForThisDept; $i++) {
                $status = $faker->randomElement(['new', 'noted', 'closed']);
                
                $ticket = Ticket::on($connectionName)->create([
                    'customer_name' => $faker->name,
                    'customer_email' => $faker->email,
                    'customer_phone' => $faker->phoneNumber,
                    'subject' => $faker->sentence(4),
                    'message' => $faker->paragraph(3),
                    'status' => $status,
                    'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
                    'updated_at' => now(),
                ]);

                // If status is 'noted', ensure at least one admin note exists
                if ($status === 'noted') {
                    AdminNote::on($connectionName)->create([
                        'ticket_id' => $ticket->id,
                        'note' => '<div>' . $faker->paragraph(2) . '</div>', // Simulate HTML content from Trix
                        'created_at' => $ticket->created_at->addHours(rand(1, 48)),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        $this->command->info('Ticket seeding completed!');
    }
}

