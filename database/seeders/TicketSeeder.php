<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;
use App\Models\Tag; // <-- IDINAGDAG
use App\Models\Customer; // <-- Idinagdag para mas malinis
use App\Models\HelpdeskTeam; // <-- Idinagdag para mas malinis
use App\Models\User; // <-- Idinagdag para mas malinis

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        // Ginamit ang na-import na models
        $customers = Customer::all();
        $teams = HelpdeskTeam::all();
        $agents = User::whereHas('roles', function($query) {
            $query->where('name', 'agent');
        })->get();


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

        // INAYOS ANG LOOP
        foreach ($tickets as $ticketData) { // Pinalitan ang $ticket ng $ticketData
            $ticketData['customer_id'] = $customers->random()->id;
            $ticketData['team_id'] = $teams->random()->id;
            $ticketData['assigned_to_user_id'] = $agents->random()->id;
            
            // I-CREATE ANG TICKET AT I-SAVE SA VARIABLE
            $newTicket = Ticket::create($ticketData);
            
            // I-ATTACH ANG RANDOM TAGS
            // Kumuha ng 1 hanggang 3 random tags
            $randomTagIds = $tagIds->random(rand(1, min(3, $tagIds->count())));
            
            // I-attach ang mga ito sa ticket na kakagawa lang
            $newTicket->tags()->attach($randomTagIds);
        }
    }
}