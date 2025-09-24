<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@solafriq.com',
            'password' => 'password',
            'role' => 'ADMIN',
            'phone' => '+1-800-123-4567',
            'address' => 'New York, USA',
        ]);

        // Create sample client
        \App\Models\User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'role' => 'CLIENT',
            'phone' => '+1-801-234-5678',
            'address' => 'Los Angeles, USA',
        ]);
    }
}
