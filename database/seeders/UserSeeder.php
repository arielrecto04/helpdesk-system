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

        $adminRole = Role::where('name', 'admin')->first();
        $agentRole = Role::where('name', 'agent')->first();

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

        for ($i = 1; $i <= 5; $i++) {
            $agent = User::firstOrCreate(
                ['email' => "agent{$i}@helpdesk.com"],
                [
                    'first_name' => "Support",
                    'middle_name' => "Agent",
                    'last_name' => "Agent {$i}",
                    'password' => bcrypt('password'),
                ]
            );

            if ($agentRole && !$agent->roles()->where('role_id', $agentRole->id)->exists()) {
                $agent->roles()->attach($agentRole->id);
            }
        }
    }
}