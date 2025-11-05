<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\HelpdeskTeam;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Validation\Rule;
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
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Tickets/Create', [
            'customers' => Customer::all(['id', 'first_name', 'last_name']),
            'teams' => HelpdeskTeam::all(),
            'users' => User::all(['id', 'first_name', 'last_name']),
            'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            'stages' => ['Open', 'In Progress', 'Pending Customer', 'Resolved', 'Closed'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High', 'Urgent'])],
            'stage' => ['required', Rule::in(['Open', 'In Progress', 'Pending Customer', 'Resolved', 'Closed'])],
            'team_id' => 'required|exists:helpdesk_teams,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
        ]);

        $ticket = Ticket::create($validated);

        return redirect()->route('tickets.show', $ticket->id)->with('message', 'Ticket created successfully.');
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Inertia\Response
     */
    public function edit(Ticket $ticket): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

        if (!$isAdmin && !$hasTeamAccess) {
            abort(403, 'Unauthorized. You can only edit tickets from your teams.');
        }

        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket,
            'customers' => Customer::all(['id', 'first_name', 'last_name']),
            'teams' => HelpdeskTeam::all(),
            'users' => User::all(['id', 'first_name', 'last_name']),
            'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            'stages' => ['Open', 'In Progress', 'Pending Customer', 'Resolved', 'Closed'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

        if (!$isAdmin && !$hasTeamAccess) {
            abort(403, 'Unauthorized. You can only update tickets from your teams.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High', 'Urgent'])],
            'stage' => ['required', Rule::in(['Open', 'In Progress', 'Pending Customer', 'Resolved', 'Closed'])],
            'team_id' => 'required|exists:helpdesk_teams,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.show', $ticket->id)->with('message', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

        if (!$isAdmin && !$hasTeamAccess) {
            abort(403, 'Unauthorized. You can only delete tickets from your teams.');
        }

        $teamId = $ticket->team_id;
        $ticket->delete();

        // Redirect to the team's ticket list after deletion
        return Redirect::route('team.tickets', ['teamId' => $teamId])
            ->with('message', 'Ticket deleted successfully.');
    }
}