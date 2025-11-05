<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import ang Auth
use Inertia\Inertia;
use Inertia\Response;

class MyTicketController extends Controller
{
    public function index(): Response
    {
        $tickets = Ticket::query()
            ->where('assigned_to_user_id', Auth::id()) 
            ->with(['customer', 'team', 'assignedTo'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('MyTickets', [ 
            'tickets' => $tickets,
            'pageTitle' => 'My Tickets'
        ]);
    }
}