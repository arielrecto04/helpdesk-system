<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = [
            [
                'title' => 'Printer not working',
                'type' => 'ticket',
                'status' => 'New',
            ],
            [
                'title' => 'Network issue resolved',
                'type' => 'response',
                'status' => 'Resolved',
            ],
            [
                'title' => 'Software installation request',
                'type' => 'assignment',
                'status' => 'In Progress',
            ],
        ];

        foreach ($tickets as $ticket) {
            Ticket::create($ticket);
        }
    }
}