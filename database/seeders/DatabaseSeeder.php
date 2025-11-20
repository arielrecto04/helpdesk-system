<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            PositionSeeder::class,
            HelpdeskTeamSeeder::class,
            CompanySeeder::class,
            RolePermissionSeeder::class, // Run this before UserSeeder so roles exist
            UserSeeder::class, 
            EmployeeSeeder::class,
            CustomerSeeder::class,
            TagSeeder::class,
            TicketSeeder::class,
        ]);
    }
}