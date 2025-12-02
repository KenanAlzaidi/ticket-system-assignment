<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create if it doesn't exist to avoid duplicate errors on re-runs
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'username' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => 'password', // Will be hashed by the model casts
                'role' => 'admin',
            ]);
            $this->command->info('Admin user created: admin@example.com / password');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}

