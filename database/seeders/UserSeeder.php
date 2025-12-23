<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role; 
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {

        // Support role names created by RolePermissionSeeder such as
        // 'Admin' and location-specific agent roles like 'Agent (Cebu)'.
        $adminRole = Role::where('name', 'admin')
            ->orWhere('name', 'Admin')
            ->first();

        $agentRole = Role::where(function ($q) {
            $q->where('name', 'agent')
              ->orWhere('name', 'Agent')
              ->orWhere('name', 'like', 'Agent%');
        })->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@helpdesk.com'],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'password' => bcrypt('password'),
            ]
        );

        if ($adminRole && !$admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole->id);
        }
    }
}