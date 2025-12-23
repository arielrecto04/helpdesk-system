<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;
use App\Models\Tag;
use App\Models\Customer;
use App\Models\HelpdeskTeam;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $teams = HelpdeskTeam::all();
        $agentUsers = User::whereHas('roles', function($query) {
            $query->where('name', 'agent');
        })->get();

        $agents = \App\Models\Employee::whereIn('user_id', $agentUsers->pluck('id'))->get();
        if ($agents->isEmpty()) {
            $this->command->warn('No employees are linked to agent user accounts. Falling back to any Employee records.');
            $agents = \App\Models\Employee::all();
        }


        $tagIds = Tag::pluck('id');

        if ($customers->isEmpty() || $teams->isEmpty() || $agents->isEmpty() || $tagIds->isEmpty()) {
            $this->command->error('ERROR: Paki-check kung may laman ang Customer, Team, User(Agent), at Tag seeders bago i-run ang TicketSeeder.');
            return;
        }

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
                'stage' => 'Open',
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
            ],
            [
                'subject' => 'Monitor flickering intermittently',
                'description' => 'My monitor flickers when I open multiple browser tabs.',
                'priority' => 'Low',
                'stage' => 'Open',
                'deadline' => now()->addDays(3)
            ],
            [
                'subject' => 'ERP system slow during peak hours',
                'description' => 'ERP response time becomes very slow around 10AM-2PM.',
                'priority' => 'High',
                'stage' => 'In Progress',
                'deadline' => now()->addDays(2)
            ],
            [
                'subject' => 'Mobile app login issue',
                'description' => 'Customers report they cannot login to the mobile app.',
                'priority' => 'Urgent',
                'stage' => 'Open',
                'deadline' => now()->addDay()
            ],
            [
                'subject' => 'Database backup failed last night',
                'description' => 'Automated backup job returned exit code 1.',
                'priority' => 'High',
                'stage' => 'Open',
                'deadline' => now()->addDays(1)
            ],
            [
                'subject' => 'VPN access request for remote employee',
                'description' => 'Please grant VPN access to contractor for 2 weeks.',
                'priority' => 'Medium',
                'stage' => 'Resolved',
                'deadline' => now()->addDays(7)
            ],
            [
                'subject' => 'Two-factor authentication setup',
                'description' => 'Need assistance setting up 2FA on my account.',
                'priority' => 'Low',
                'stage' => 'Closed',
                'deadline' => now()->addDays(4)
            ],
            [
                'subject' => 'Broken office chair',
                'description' => 'The adjustment lever is broken and the chair tilts forward.',
                'priority' => 'Low',
                'stage' => 'Open',
                'deadline' => now()->addDays(10)
            ],
            [
                'subject' => 'Projector not powering on in Training Room',
                'description' => 'Projector shows no lights when switched on.',
                'priority' => 'Medium',
                'stage' => 'In Progress',
                'deadline' => now()->addDays(2)
            ],
            [
                'subject' => 'Access rights for shared drive',
                'description' => 'Request access to Marketing shared drive for new hire.',
                'priority' => 'High',
                'stage' => 'Open',
                'deadline' => now()->addDays(1)
            ],
            [
                'subject' => 'Billing discrepancy on invoice #4521',
                'description' => 'Customer claims duplicate charge on last invoice.',
                'priority' => 'Urgent',
                'stage' => 'In Progress',
                'deadline' => now()->addDay()
            ],
            [
                'subject' => 'Request for new mouse',
                'description' => 'Wireless mouse that was provided is malfunctioning.',
                'priority' => 'Low',
                'stage' => 'Resolved',
                'deadline' => now()->addDays(5)
            ],
            [
                'subject' => 'Calendar sync issue with Outlook',
                'description' => 'Events from Google Calendar are not syncing to Outlook.',
                'priority' => 'Medium',
                'stage' => 'Open',
                'deadline' => now()->addDays(3)
            ],
            [
                'subject' => 'Phishing email reported',
                'description' => 'Multiple staff received a suspicious email asking for credentials.',
                'priority' => 'High',
                'stage' => 'In Progress',
                'deadline' => now()->addDay()
            ],
            [
                'subject' => 'Phone line static on extension 204',
                'description' => 'There is constant static noise during calls.',
                'priority' => 'Medium',
                'stage' => 'Open',
                'deadline' => now()->addDays(4)
            ],
            [
                'subject' => 'Antivirus false positive on shared folder',
                'description' => 'Antivirus quarantined several harmless files.',
                'priority' => 'High',
                'stage' => 'Open',
                'deadline' => now()->addDays(2)
            ],
            [
                'subject' => 'RAM upgrade request for design workstation',
                'description' => 'Request to upgrade RAM from 8GB to 16GB for better performance.',
                'priority' => 'Medium',
                'stage' => 'Open',
                'deadline' => now()->addDays(7)
            ],
            [
                'subject' => 'Server room cable replacement',
                'description' => 'Replace damaged CAT6 cable in rack 3.',
                'priority' => 'High',
                'stage' => 'In Progress',
                'deadline' => now()->addDays(1)
            ],
            [
                'subject' => 'CCTV footage access for incident on 11/20',
                'description' => 'Security requests access to CCTV recordings for review.',
                'priority' => 'Urgent',
                'stage' => 'Open',
                'deadline' => now()->addDay()
            ]
        ];

        $hasClosedAt = Schema::hasColumn('tickets', 'closed_at');

        foreach ($tickets as $ticketData) {
            $ticketData['customer_id'] = $customers->random()->id;
            $ticketData['team_id'] = $teams->random()->id;

            if (in_array($ticketData['stage'], ['Resolved', 'Closed'])) {
                $ticketData['employee_id'] = $agents->random()->id;
                if ($hasClosedAt) {
                    // closed_at sometime in the past 0-7 days
                    $ticketData['closed_at'] = now()->subDays(rand(0, 7));
                }
            } else {
                // For open/in-progress tickets, randomly leave some unassigned
                $ticketData['employee_id'] = (rand(0, 4) === 0) ? null : $agents->random()->id;
            }

            $newTicket = Ticket::create($ticketData);
            $randomTagIds = $tagIds->random(rand(1, min(3, $tagIds->count())));
            $newTicket->tags()->attach($randomTagIds);
        }
    }
}