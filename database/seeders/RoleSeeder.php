<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'description' => 'System Administrator with full access'],
            ['name' => 'Helpdesk Manager', 'description' => 'Manages helpdesk operations and teams'],
            ['name' => 'Support Agent', 'description' => 'Handles customer tickets and provides support'],
            ['name' => 'Customer', 'description' => 'End user who can create and track tickets']
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}