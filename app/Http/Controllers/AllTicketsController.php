<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Customer;
use App\Models\HelpdeskTeam;
use Illuminate\Http\RedirectResponse;

class AllTicketsController extends Controller
{
    public function index(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Ticket::query()
            ->with(['customer', 'team', 'assignedTo'])
            ->latest();

        // Only show all tickets when user is admin or has broad view permissions
        $hasBroadView = $user->hasRole('admin')
            || $user->hasPermissionTo('view_all_tickets')
            || $user->hasPermissionTo('can_view_other_teams_tickets')
            || $user->hasPermissionTo('can_view_other_locations_tickets')
            || $user->hasPermissionTo('can_view_other_users_tickets');

        if (!$hasBroadView) {
            $teamIds = $user->teams()->pluck('helpdesk_teams.id')->toArray();
            $query->where(function ($q) use ($teamIds, $user) {
                $q->whereIn('team_id', $teamIds)
                    ->orWhere('assigned_to_user_id', $user->id);
            });
        }

        $alltickets = $query->paginate(15)->withQueryString();

        return Inertia::render('AllTickets', [
            'tickets' => $alltickets,
            'pageTitle' => 'All Tickets'
        ]);
    }

    public function create(): Response
    {
        $customers = Customer::latest()->get(['id', 'first_name', 'last_name']);
        $teams = HelpdeskTeam::latest()->get(['id', 'name']);

        return Inertia::render('AllTickets/Create', [
            'customers' => $customers->map(fn($c) => ['id' => $c->id, 'name' => $c->first_name . ' ' . $c->last_name]),
            'teams' => $teams,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'required|exists:customers,id',
            'team_id' => 'required|exists:helpdesk_teams,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
        ]);

        Ticket::create($validated);

        return redirect()->route('alltickets.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket): Response
    {
        $ticket->load(['customer', 'team', 'assignedTo', 'tags']);

        return Inertia::render('AllTickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function edit(Ticket $ticket): Response
    {
        $customers = Customer::latest()->get(['id', 'first_name', 'last_name']);
        $teams = HelpdeskTeam::latest()->get(['id', 'name']);

        return Inertia::render('AllTickets/Edit', [
            'ticket' => $ticket,
            'customers' => $customers->map(fn($c) => ['id' => $c->id, 'name' => $c->first_name . ' ' . $c->last_name]),
            'teams' => $teams,
        ]);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'required|exists:customers,id',
            'team_id' => 'required|exists:helpdesk_teams,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
        ]);

        $ticket->update($validated);

        return redirect()->route('alltickets.index')->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();
        return redirect()->route('alltickets.index')->with('success', 'Ticket deleted successfully.');
    }
}