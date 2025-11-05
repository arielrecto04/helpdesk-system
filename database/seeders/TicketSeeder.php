<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $customers = \App\Models\Customer::all();
        $teams = \App\Models\HelpdeskTeam::all();
        $agents = \App\Models\User::whereHas('roles', function($query) {
            $query->where('name', 'Support Agent');
        })->get();

        $tickets = [
            [
                'subject' => 'Printer not working in Finance Department',
                'description' => 'The HP LaserJet printer on the 2nd floor is not responding to print commands.',
                'priority' => 'Medium',
                'stage' => 'Open',
                'deadline' => now()->addDays(2)
            ],
            [
                'subject' => 'Email server down',
                'description' => 'Unable to send or receive emails. Affecting all departments.',
                'priority' => 'Urgent',
                'stage' => 'In Progress',
                'deadline' => now()->addDay()
            ],
            [
                'subject' => 'New software installation request',
                'description' => 'Need Visual Studio Code installed on my workstation.',
                'priority' => 'Low',
                'stage' => 'Pending Customer',
                'deadline' => now()->addDays(5)
            ],
            [
                'subject' => 'Network connectivity issues',
                'description' => 'Intermittent network drops in the Marketing department.',
                'priority' => 'High',
                'stage' => 'Open',
                'deadline' => now()->addDays(1)
            ],
            [
                'subject' => 'Password reset request',
                'description' => 'Unable to login to my workstation after system update.',
                'priority' => 'Medium',
                'stage' => 'Resolved',
                'deadline' => now()->addDays(1)
            ]
        ];

        foreach ($tickets as $ticket) {
            $ticket['customer_id'] = $customers->random()->id;
            $ticket['team_id'] = $teams->random()->id;
            $ticket['assigned_to_user_id'] = $agents->random()->id;
            
            Ticket::create($ticket);
        }
    }
}