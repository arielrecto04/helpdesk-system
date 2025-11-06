<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Inertia\Inertia;
use Inertia\Response;

class AllTicketsController extends Controller
{
    public function index(): Response
    {
        $alltickets = Ticket::query()
            ->with(['customer', 'team', 'assignedTo'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('AllTickets', [
            'tickets' => $alltickets,
            'pageTitle' => 'All Tickets'
        ]);
    }
}