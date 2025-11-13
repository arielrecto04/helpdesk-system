<?php

namespace Database\Seeders;

use App\Models\User;
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
            RoleSeeder::class,
            HelpdeskTeamSeeder::class,
        ]);

        $admin = User::create([
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@helpdesk.com',
            'password' => bcrypt('password'),
        ]);

        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'first_name' => "Support",
                'middle_name' => "Agent",
                'last_name' => "Agent {$i}",
                'email' => "agent{$i}@helpdesk.com",
                'password' => bcrypt('password'),
            ]);
        }

        // Assign roles to users
        $adminRole = \App\Models\Role::where('name', 'Admin')->first();
        $agentRole = \App\Models\Role::where('name', 'Support Agent')->first();
        
        $admin->roles()->attach($adminRole->id);
        
        User::where('email', 'like', 'agent%')->get()->each(function ($user) use ($agentRole) {
            $user->roles()->attach($agentRole->id);
        });

        // Create customers and tickets
        $this->call([
            CustomerSeeder::class,
            TicketSeeder::class,
        ]);
    }
}
