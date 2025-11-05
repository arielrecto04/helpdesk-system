<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\HelpdeskTeam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of all tickets.
     *
     * @return Response
     */
    public function index(): Response
    {


        $tickets = Ticket::query()
            ->with(['customer', 'team', 'assignedTo'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Tickets/AllTickets', [
            'tickets' => $tickets,
            'pageTitle' => 'All Tickets'
        ]);
    }
    /**
     * Display the specified ticket.
     *
     * @param Ticket $ticket
     * @return Response
     */
    public function show(Ticket $ticket): Response
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Check if user is admin
        $isAdmin = $user->hasRole('admin');
        
        // Check if user has access to the ticket's team
        
        $hasTeamAccess = $user->teams()
            ->where('helpdesk_teams.id', $ticket->team_id)
            ->exists();
        
        // Authorize access
        if (!$isAdmin && !$hasTeamAccess) {
            abort(403, 'Unauthorized. You can only view tickets from your teams.');
        }

        // Load relationships and return the view
        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket->load(['customer', 'assignedTo', 'team']),
            'isAdmin' => $isAdmin
        ]);
    }
}