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

        $adminRole = Role::where('name', 'Admin')->first();
        $agentRole = Role::where('name', 'Support Agent')->first();

        $admin = User::create([
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@helpdesk.com',
            'password' => bcrypt('password'),
        ]);

        $admin->roles()->attach($adminRole->id);

        for ($i = 1; $i <= 5; $i++) {
            $agent = User::create([
                'first_name' => "Support",
                'middle_name' => "Agent",
                'last_name' => "Agent {$i}",
                'email' => "agent{$i}@helpdesk.com",
                'password' => bcrypt('password'),
            ]);

            $agent->roles()->attach($agentRole->id);
        }
    }
}